'use client'

import { useState, useRef } from 'react'
import { Upload, FileText, CheckCircle, AlertCircle, Loader2 } from 'lucide-react'
import { Button } from '@/components/ui/Button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card'
import { Input } from '@/components/ui/Input'
import { validateFile, getDocumentType } from '@/lib/validation'

interface UploadState {
  file: File | null
  fileName: string
  documentType: string
  isUploading: boolean
  isProcessing: boolean
  error: string | null
  success: boolean
}

export default function IngestPage() {
  const [uploadState, setUploadState] = useState<UploadState>({
    file: null,
    fileName: '',
    documentType: '',
    isUploading: false,
    isProcessing: false,
    error: null,
    success: false
  })

  const fileInputRef = useRef<HTMLInputElement>(null)

  const handleFileSelect = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0]
    if (!file) return

    // Валидация файла
    const validation = validateFile(file)
    if (!validation.valid) {
      setUploadState(prev => ({
        ...prev,
        error: validation.error || 'Ошибка валидации файла'
      }))
      return
    }

    const documentType = getDocumentType(file.name)

    setUploadState(prev => ({
      ...prev,
      file,
      fileName: file.name,
      documentType,
      error: null,
      success: false
    }))
  }

  const handleUpload = async () => {
    if (!uploadState.file) return

    setUploadState(prev => ({
      ...prev,
      isUploading: true,
      error: null
    }))

    try {
      const formData = new FormData()
      formData.append('file', uploadState.file)

      const response = await fetch('/api/ingest/upload', {
        method: 'POST',
        body: formData
      })

      if (!response.ok) {
        const error = await response.json()
        throw new Error(error.error || 'Ошибка загрузки файла')
      }

      const result = await response.json()

      // Начинаем обработку документа
      setUploadState(prev => ({
        ...prev,
        isUploading: false,
        isProcessing: true
      }))

      const processResponse = await fetch('/api/ingest/build', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          fileName: result.fileName,
          documentType: result.documentType,
          originalName: result.originalName
        })
      })

      if (!processResponse.ok) {
        const error = await processResponse.json()
        throw new Error(error.error || 'Ошибка обработки документа')
      }

      const processResult = await processResponse.json()

      setUploadState(prev => ({
        ...prev,
        isProcessing: false,
        success: true
      }))

    } catch (error) {
      setUploadState(prev => ({
        ...prev,
        isUploading: false,
        isProcessing: false,
        error: error instanceof Error ? error.message : 'Неизвестная ошибка'
      }))
    }
  }

  const handleDragOver = (event: React.DragEvent) => {
    event.preventDefault()
  }

  const handleDrop = (event: React.DragEvent) => {
    event.preventDefault()
    const file = event.dataTransfer.files[0]
    if (file) {
      const validation = validateFile(file)
      if (!validation.valid) {
        setUploadState(prev => ({
          ...prev,
          error: validation.error || 'Ошибка валидации файла'
        }))
        return
      }

      const documentType = getDocumentType(file.name)
      setUploadState(prev => ({
        ...prev,
        file,
        fileName: file.name,
        documentType,
        error: null,
        success: false
      }))
    }
  }

  const resetForm = () => {
    setUploadState({
      file: null,
      fileName: '',
      documentType: '',
      isUploading: false,
      isProcessing: false,
      error: null,
      success: false
    })
    if (fileInputRef.current) {
      fileInputRef.current.value = ''
    }
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-2xl mx-auto">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-dark-900 mb-4">
            Загрузка модуля
          </h1>
          <p className="text-dark-600">
            Загрузите PDF, Markdown или текстовый файл с описанием модуля для автоматической обработки
          </p>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Выберите файл</CardTitle>
          </CardHeader>
          <CardContent>
            <div
              className="border-2 border-dashed border-dark-300 rounded-lg p-8 text-center hover:border-primary-400 transition-colors"
              onDragOver={handleDragOver}
              onDrop={handleDrop}
            >
              <Upload className="w-12 h-12 text-dark-400 mx-auto mb-4" />
              <p className="text-dark-600 mb-4">
                Перетащите файл сюда или нажмите для выбора
              </p>
              <input
                ref={fileInputRef}
                type="file"
                accept=".pdf,.md,.txt,.json"
                onChange={handleFileSelect}
                className="hidden"
              />
              <Button
                variant="outline"
                onClick={() => fileInputRef.current?.click()}
              >
                Выбрать файл
              </Button>
            </div>

            {uploadState.file && (
              <div className="mt-6 p-4 bg-dark-50 rounded-lg">
                <div className="flex items-center">
                  <FileText className="w-5 h-5 text-primary-600 mr-3" />
                  <div className="flex-1">
                    <p className="font-medium text-dark-900">{uploadState.fileName}</p>
                    <p className="text-sm text-dark-600">
                      Тип: {uploadState.documentType} • Размер: {(uploadState.file.size / 1024 / 1024).toFixed(2)} MB
                    </p>
                  </div>
                </div>
              </div>
            )}

            {uploadState.error && (
              <div className="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div className="flex items-center">
                  <AlertCircle className="w-5 h-5 text-red-600 mr-3" />
                  <p className="text-red-800">{uploadState.error}</p>
                </div>
              </div>
            )}

            {uploadState.success && (
              <div className="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div className="flex items-center">
                  <CheckCircle className="w-5 h-5 text-green-600 mr-3" />
                  <p className="text-green-800">
                    Документ успешно загружен и обработан!
                  </p>
                </div>
              </div>
            )}

            <div className="mt-6 flex gap-3">
              <Button
                onClick={handleUpload}
                disabled={!uploadState.file || uploadState.isUploading || uploadState.isProcessing}
                loading={uploadState.isUploading || uploadState.isProcessing}
                className="flex-1"
              >
                {uploadState.isUploading ? 'Загрузка...' : 
                 uploadState.isProcessing ? 'Обработка...' : 'Загрузить и обработать'}
              </Button>
              
              {uploadState.success && (
                <Button
                  variant="outline"
                  onClick={resetForm}
                >
                  Загрузить еще
                </Button>
              )}
            </div>
          </CardContent>
        </Card>

        <Card className="mt-6">
          <CardHeader>
            <CardTitle>Поддерживаемые форматы</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid md:grid-cols-2 gap-4">
              <div>
                <h4 className="font-medium text-dark-900 mb-2">PDF (.pdf)</h4>
                <p className="text-sm text-dark-600">
                  Модули в формате PDF с текстовым содержимым
                </p>
              </div>
              <div>
                <h4 className="font-medium text-dark-900 mb-2">Markdown (.md)</h4>
                <p className="text-sm text-dark-600">
                  Документы в формате Markdown с разметкой
                </p>
              </div>
              <div>
                <h4 className="font-medium text-dark-900 mb-2">Текст (.txt)</h4>
                <p className="text-sm text-dark-600">
                  Простые текстовые файлы с описанием модуля
                </p>
              </div>
              <div>
                <h4 className="font-medium text-dark-900 mb-2">JSON (.json)</h4>
                <p className="text-sm text-dark-600">
                  Структурированные данные в формате JSON
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
