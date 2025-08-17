import { NextRequest, NextResponse } from 'next/server'
import { writeFile } from 'fs/promises'
import { join } from 'path'
import { validateFile, getDocumentType } from '@/lib/validation'

export async function POST(request: NextRequest) {
  try {
    const formData = await request.formData()
    const file = formData.get('file') as File

    if (!file) {
      return NextResponse.json(
        { error: 'Файл не найден' },
        { status: 400 }
      )
    }

    // Валидация файла
    const validation = validateFile(file)
    if (!validation.valid) {
      return NextResponse.json(
        { error: validation.error },
        { status: 400 }
      )
    }

    // Определение типа документа
    const documentType = getDocumentType(file.name)

    // Сохранение файла во временную папку
    const bytes = await file.arrayBuffer()
    const buffer = Buffer.from(bytes)
    
    const uploadDir = join(process.cwd(), 'uploads')
    const fileName = `${Date.now()}-${file.name}`
    const filePath = join(uploadDir, fileName)
    
    await writeFile(filePath, buffer)

    // Возвращаем информацию о загруженном файле
    return NextResponse.json({
      success: true,
      fileName,
      filePath,
      documentType,
      size: file.size,
      originalName: file.name
    })

  } catch (error) {
    console.error('Ошибка загрузки файла:', error)
    return NextResponse.json(
      { error: 'Ошибка загрузки файла' },
      { status: 500 }
    )
  }
}
