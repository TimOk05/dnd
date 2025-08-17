'use client'

import { useState, useEffect } from 'react'
import { useParams, useRouter } from 'next/navigation'
import { Button } from '@/components/ui/Button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card'
import { 
  ArrowLeft, 
  RefreshCw, 
  Copy, 
  Dice,
  Download
} from 'lucide-react'

interface RandomItem {
  [key: string]: any
}

export default function RandomTablePage() {
  const params = useParams()
  const router = useRouter()
  const tableName = params.name as string

  const [items, setItems] = useState<RandomItem[]>([])
  const [isLoading, setIsLoading] = useState(false)
  const [tableInfo, setTableInfo] = useState<any>(null)

  useEffect(() => {
    loadTableInfo()
    generateRandomItem()
  }, [tableName])

  const loadTableInfo = async () => {
    try {
      const response = await fetch(`/api/tables/${tableName}`)
      if (response.ok) {
        const data = await response.json()
        setTableInfo(data.table)
      }
    } catch (error) {
      console.error('Ошибка загрузки информации о таблице:', error)
    }
  }

  const generateRandomItem = async () => {
    setIsLoading(true)
    try {
      const response = await fetch(`/api/tables/${tableName}?action=random`)
      if (response.ok) {
        const data = await response.json()
        if (data.item) {
          setItems([data.item])
        }
      }
    } catch (error) {
      console.error('Ошибка генерации случайного элемента:', error)
    } finally {
      setIsLoading(false)
    }
  }

  const generateMultipleItems = async (count: number) => {
    setIsLoading(true)
    try {
      const promises = Array(count).fill(0).map(() => 
        fetch(`/api/tables/${tableName}?action=random`)
      )
      const responses = await Promise.all(promises)
      const results = await Promise.all(responses.map(r => r.json()))
      
      const newItems = results
        .filter(r => r.item)
        .map(r => r.item)
      
      setItems(newItems)
    } catch (error) {
      console.error('Ошибка генерации элементов:', error)
    } finally {
      setIsLoading(false)
    }
  }

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text)
  }

  const copyAllToClipboard = () => {
    const allText = items.map(item => 
      Object.entries(item)
        .map(([key, value]) => `${key}: ${value}`)
        .join('\n')
    ).join('\n\n---\n\n')
    
    navigator.clipboard.writeText(allText)
  }

  const exportToJSON = () => {
    const dataStr = JSON.stringify(items, null, 2)
    const blob = new Blob([dataStr], { type: 'application/json' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${tableName}_random.json`
    a.click()
    window.URL.revokeObjectURL(url)
  }

  const getTableDisplayName = (name: string) => {
    const names: Record<string, string> = {
      drinks: 'Напитки',
      npcs: 'NPC',
      potions: 'Зелья',
      events_travel: 'События в пути',
      tavern_names: 'Названия таверн'
    }
    return names[name] || name
  }

  const getTableIcon = (name: string) => {
    const icons: Record<string, string> = {
      drinks: '🍺',
      npcs: '👤',
      potions: '🧪',
      events_travel: '🗡️',
      tavern_names: '🏪'
    }
    return icons[name] || '🎲'
  }

  return (
    <div className="max-w-4xl mx-auto p-6">
      {/* Заголовок */}
      <div className="flex justify-between items-center mb-8">
        <div className="flex items-center">
          <Button 
            variant="outline" 
            onClick={() => router.push(`/tables/${tableName}`)}
            className="mr-4"
          >
            <ArrowLeft className="w-4 h-4 mr-2" />
            Назад
          </Button>
          <div>
            <h1 className="text-3xl font-bold text-primary flex items-center">
              <span className="mr-3">{getTableIcon(tableName)}</span>
              Случайный {getTableDisplayName(tableName).toLowerCase()}
            </h1>
            <p className="text-gray-600">
              Генерация случайных элементов из таблицы
            </p>
          </div>
        </div>
      </div>

      {/* Кнопки генерации */}
      <Card className="mb-6">
        <CardHeader>
          <CardTitle>Генерация</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex flex-wrap gap-4">
            <Button
              variant="primary"
              onClick={generateRandomItem}
              disabled={isLoading}
            >
              <Dice className="w-4 h-4 mr-2" />
              {isLoading ? 'Генерирую...' : 'Случайный элемент'}
            </Button>
            
            <Button
              variant="outline"
              onClick={() => generateMultipleItems(3)}
              disabled={isLoading}
            >
              <RefreshCw className="w-4 h-4 mr-2" />
              3 элемента
            </Button>
            
            <Button
              variant="outline"
              onClick={() => generateMultipleItems(5)}
              disabled={isLoading}
            >
              <RefreshCw className="w-4 h-4 mr-2" />
              5 элементов
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Результаты */}
      {items.length > 0 && (
        <Card>
          <CardHeader>
            <div className="flex justify-between items-center">
              <CardTitle>Результаты ({items.length})</CardTitle>
              <div className="flex gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={copyAllToClipboard}
                >
                  <Copy className="w-4 h-4 mr-2" />
                  Копировать все
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={exportToJSON}
                >
                  <Download className="w-4 h-4 mr-2" />
                  Экспорт JSON
                </Button>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div className="space-y-6">
              {items.map((item, index) => (
                <div key={index} className="border rounded-lg p-4">
                  <div className="flex justify-between items-start mb-3">
                    <h3 className="font-medium text-lg">
                      Элемент #{index + 1}
                    </h3>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => copyToClipboard(
                        Object.entries(item)
                          .map(([key, value]) => `${key}: ${value}`)
                          .join('\n')
                      )}
                    >
                      <Copy className="w-4 h-4" />
                    </Button>
                  </div>
                  
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {Object.entries(item).map(([key, value]) => (
                      <div key={key} className="space-y-1">
                        <div className="text-sm font-medium text-gray-600 capitalize">
                          {key.replace(/_/g, ' ')}
                        </div>
                        <div className="text-base">
                          {typeof value === 'string' ? value : JSON.stringify(value)}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Пустое состояние */}
      {items.length === 0 && !isLoading && (
        <Card>
          <CardContent className="text-center py-12">
            <div className="text-6xl mb-4">🎲</div>
            <h3 className="text-lg font-medium mb-2">
              Нажмите кнопку для генерации
            </h3>
            <p className="text-gray-600">
              Получите случайный элемент из таблицы {getTableDisplayName(tableName).toLowerCase()}
            </p>
          </CardContent>
        </Card>
      )}
    </div>
  )
}
