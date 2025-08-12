'use client'

import { useState, useEffect } from 'react'
import Link from 'next/link'
import { Button } from '@/components/ui/Button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card'
import { 
  Table as TableIcon, 
  Plus, 
  Users, 
  Wine, 
  MapPin, 
  Sword, 
  Sparkles 
} from 'lucide-react'

interface TableInfo {
  id: string
  name: string
  rowCount: number
  description: string
  icon: React.ReactNode
}

const TABLES: TableInfo[] = [
  {
    id: 'drinks',
    name: 'Напитки',
    description: 'Алкогольные и безалкогольные напитки с эффектами',
    rowCount: 0,
    icon: <Wine className="w-5 h-5" />
  },
  {
    id: 'npcs',
    name: 'NPC',
    description: 'Неигровые персонажи для взаимодействия',
    rowCount: 0,
    icon: <Users className="w-5 h-5" />
  },
  {
    id: 'potions',
    name: 'Зелья',
    description: 'Магические зелья и их свойства',
    rowCount: 0,
    icon: <Sparkles className="w-5 h-5" />
  },
  {
    id: 'events_travel',
    name: 'События в пути',
    description: 'Случайные события во время путешествий',
    rowCount: 0,
    icon: <Sword className="w-5 h-5" />
  },
  {
    id: 'tavern_names',
    name: 'Названия таверн',
    description: 'Креативные названия для таверн и постоялых дворов',
    rowCount: 0,
    icon: <MapPin className="w-5 h-5" />
  }
]

export default function TablesPage() {
  const [tables, setTables] = useState<TableInfo[]>(TABLES)
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    loadTablesStats()
  }, [])

  const loadTablesStats = async () => {
    try {
      const response = await fetch('/api/tables')
      if (response.ok) {
        const data = await response.json()
        const updatedTables = TABLES.map(table => {
          const dbTable = data.tables.find((t: any) => t.name === table.id)
          return {
            ...table,
            rowCount: dbTable ? (dbTable.rows?.length || 0) : 0
          }
        })
        setTables(updatedTables)
      }
    } catch (error) {
      console.error('Ошибка загрузки таблиц:', error)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="max-w-6xl mx-auto p-6">
      {/* Заголовок */}
      <div className="flex justify-between items-center mb-8">
        <div>
          <h1 className="text-3xl font-bold text-primary mb-2">Таблицы данных</h1>
          <p className="text-gray-600">Управление справочными данными для D&D сессий</p>
        </div>
        <Button variant="primary" onClick={() => window.location.href = '/tables/new'}>
          <Plus className="w-4 h-4 mr-2" />
          Новая таблица
        </Button>
      </div>

      {/* Статистика */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center">
              <div className="p-3 bg-blue-100 rounded-lg mr-4">
                <TableIcon className="w-6 h-6 text-blue-600" />
              </div>
              <div>
                <div className="text-2xl font-bold text-gray-900">
                  {tables.length}
                </div>
                <div className="text-sm text-gray-600">Всего таблиц</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center">
              <div className="p-3 bg-green-100 rounded-lg mr-4">
                <Plus className="w-6 h-6 text-green-600" />
              </div>
              <div>
                <div className="text-2xl font-bold text-gray-900">
                  {tables.reduce((sum, table) => sum + table.rowCount, 0)}
                </div>
                <div className="text-sm text-gray-600">Всего записей</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center">
              <div className="p-3 bg-purple-100 rounded-lg mr-4">
                <Sparkles className="w-6 h-6 text-purple-600" />
              </div>
              <div>
                <div className="text-2xl font-bold text-gray-900">
                  {tables.filter(t => t.rowCount > 0).length}
                </div>
                <div className="text-sm text-gray-600">Активных таблиц</div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Список таблиц */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {tables.map((table) => (
          <Card key={table.id} className="hover:shadow-lg transition-shadow">
            <CardHeader>
              <div className="flex items-center justify-between">
                <div className="flex items-center">
                  <div className="p-2 bg-gray-100 rounded-lg mr-3">
                    {table.icon}
                  </div>
                  <div>
                    <CardTitle className="text-lg">{table.name}</CardTitle>
                    <p className="text-sm text-gray-600">{table.description}</p>
                  </div>
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <div className="flex justify-between items-center">
                <div className="text-sm text-gray-600">
                  {table.rowCount} записей
                </div>
                <div className="flex gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => window.location.href = `/tables/${table.id}/random`}
                  >
                    Случайная
                  </Button>
                  <Button
                    variant="primary"
                    size="sm"
                    onClick={() => window.location.href = `/tables/${table.id}`}
                  >
                    Редактировать
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {/* Быстрые действия */}
      <div className="mt-8">
        <Card>
          <CardHeader>
            <CardTitle>Быстрые действия</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              <Button
                variant="outline"
                className="flex flex-col items-center p-4 h-auto"
                onClick={() => window.location.href = '/tables/drinks/random'}
              >
                <Wine className="w-6 h-6 mb-2" />
                <span className="text-sm">Случайный напиток</span>
              </Button>
              <Button
                variant="outline"
                className="flex flex-col items-center p-4 h-auto"
                onClick={() => window.location.href = '/tables/npcs/random'}
              >
                <Users className="w-6 h-6 mb-2" />
                <span className="text-sm">Случайный NPC</span>
              </Button>
              <Button
                variant="outline"
                className="flex flex-col items-center p-4 h-auto"
                onClick={() => window.location.href = '/tables/potions/random'}
              >
                <Sparkles className="w-6 h-6 mb-2" />
                <span className="text-sm">Случайное зелье</span>
              </Button>
              <Button
                variant="outline"
                className="flex flex-col items-center p-4 h-auto"
                onClick={() => window.location.href = '/tables/tavern_names/random'}
              >
                <MapPin className="w-6 h-6 mb-2" />
                <span className="text-sm">Название таверны</span>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
