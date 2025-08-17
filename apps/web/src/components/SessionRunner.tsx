'use client'

import { useState, useEffect } from 'react'
import { Button } from '@/components/ui/Button'
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/Card'
import { Input } from '@/components/ui/Input'
import { SessionStage, SessionMode, AISuggestion } from '@dm-copilot/shared'

interface SessionRunnerProps {
  sessionId?: string
}

const SESSION_STAGES: { value: SessionStage; label: string; description: string }[] = [
  { value: 'HOOK', label: 'Зацепка', description: 'Начало приключения' },
  { value: 'EXPLORATION', label: 'Исследование', description: 'Изучение мира' },
  { value: 'COMBAT', label: 'Бой', description: 'Сражения и конфликты' },
  { value: 'SOCIAL', label: 'Социальное', description: 'Дипломатия и торговля' },
  { value: 'PUZZLE', label: 'Головоломка', description: 'Загадки и ловушки' },
  { value: 'CLIMAX', label: 'Кульминация', description: 'Финальное противостояние' },
  { value: 'RESOLUTION', label: 'Развязка', description: 'Завершение приключения' }
]

const SUGGESTION_TYPES = [
  { type: 'plot_twist', label: 'Поворот сюжета', icon: '🎭' },
  { type: 'npc_dialogue', label: 'Диалог NPC', icon: '🗣️' },
  { type: 'scene_description', label: 'Описание сцены', icon: '🏰' },
  { type: 'loot_suggestion', label: 'Предложение лута', icon: '💎' },
  { type: 'skill_check', label: 'Проверка навыков', icon: '🎲' }
]

export default function SessionRunner({ sessionId }: SessionRunnerProps) {
  const [currentStage, setCurrentStage] = useState<SessionStage>('HOOK')
  const [masterNotes, setMasterNotes] = useState('')
  const [isLoading, setIsLoading] = useState(false)
  const [suggestions, setSuggestions] = useState<AISuggestion[]>([])
  const [selectedSuggestionType, setSelectedSuggestionType] = useState<string>('plot_twist')

  // Загрузка сессии при монтировании
  useEffect(() => {
    if (sessionId) {
      loadSession(sessionId)
    }
  }, [sessionId])

  const loadSession = async (id: string) => {
    try {
      const response = await fetch(`/api/sessions/${id}`)
      if (response.ok) {
        const session = await response.json()
        setCurrentStage(session.stage)
        setMasterNotes(session.notes || '')
      }
    } catch (error) {
      console.error('Ошибка загрузки сессии:', error)
    }
  }

  const updateSessionStage = async (stage: SessionStage) => {
    if (!sessionId) return

    try {
      const response = await fetch(`/api/sessions/${sessionId}/stage`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ stage })
      })

      if (response.ok) {
        setCurrentStage(stage)
      }
    } catch (error) {
      console.error('Ошибка обновления этапа:', error)
    }
  }

  const saveMasterNotes = async () => {
    if (!sessionId) return

    try {
      await fetch(`/api/sessions/${sessionId}/notes`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ notes: masterNotes })
      })
    } catch (error) {
      console.error('Ошибка сохранения заметок:', error)
    }
  }

  const generateSuggestion = async () => {
    setIsLoading(true)
    try {
      const response = await fetch('/api/orchestrate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          context: {
            sessionStage: currentStage,
            masterNotes,
            moduleSummary: 'Контекст модуля будет загружен из базы',
            recentEvents: [],
            relevantChunks: [],
            tableData: []
          },
          templateType: selectedSuggestionType
        })
      })

      if (response.ok) {
        const data = await response.json()
        setSuggestions(prev => [data.suggestion, ...prev.slice(0, 4)]) // Храним последние 5
      } else {
        const error = await response.json()
        console.error('Ошибка генерации:', error)
      }
    } catch (error) {
      console.error('Ошибка запроса к AI:', error)
    } finally {
      setIsLoading(false)
    }
  }

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text)
  }

  return (
    <div className="max-w-6xl mx-auto p-6 space-y-6">
      {/* Заголовок сессии */}
      <div className="flex justify-between items-center">
        <h1 className="text-3xl font-bold text-primary">Сессия D&D</h1>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => window.history.back()}>
            Назад
          </Button>
          <Button variant="primary">
            Экспорт сессии
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Левая панель - Управление сессией */}
        <div className="space-y-6">
          {/* Этап сессии */}
          <Card>
            <CardHeader>
              <CardTitle>Этап сессии</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                {SESSION_STAGES.map((stage) => (
                  <Button
                    key={stage.value}
                    variant={currentStage === stage.value ? 'primary' : 'outline'}
                    className="w-full justify-start"
                    onClick={() => updateSessionStage(stage.value)}
                  >
                    <div className="text-left">
                      <div className="font-medium">{stage.label}</div>
                      <div className="text-sm opacity-75">{stage.description}</div>
                    </div>
                  </Button>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Заметки мастера */}
          <Card>
            <CardHeader>
              <CardTitle>Заметки мастера</CardTitle>
            </CardHeader>
            <CardContent>
              <textarea
                value={masterNotes}
                onChange={(e) => setMasterNotes(e.target.value)}
                onBlur={saveMasterNotes}
                placeholder="Запишите важные детали, планы, идеи..."
                className="w-full h-32 p-3 border rounded-lg resize-none focus:ring-2 focus:ring-primary focus:border-transparent"
              />
            </CardContent>
          </Card>
        </div>

        {/* Центральная панель - Генерация подсказок */}
        <div className="space-y-6">
          {/* Генератор подсказок */}
          <Card>
            <CardHeader>
              <CardTitle>AI Подсказки</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {/* Выбор типа подсказки */}
              <div>
                <label className="block text-sm font-medium mb-2">Тип подсказки</label>
                <div className="grid grid-cols-2 gap-2">
                  {SUGGESTION_TYPES.map((type) => (
                    <Button
                      key={type.type}
                      variant={selectedSuggestionType === type.type ? 'primary' : 'outline'}
                      size="sm"
                      onClick={() => setSelectedSuggestionType(type.type)}
                    >
                      <span className="mr-2">{type.icon}</span>
                      {type.label}
                    </Button>
                  ))}
                </div>
              </div>

              {/* Кнопка генерации */}
              <Button
                onClick={generateSuggestion}
                disabled={isLoading}
                className="w-full"
                variant="primary"
              >
                {isLoading ? 'Генерирую...' : 'Сгенерировать подсказку'}
              </Button>
            </CardContent>
          </Card>

          {/* История подсказок */}
          <Card>
            <CardHeader>
              <CardTitle>История подсказок</CardTitle>
            </CardHeader>
            <CardContent>
              {suggestions.length === 0 ? (
                <p className="text-center text-gray-500 py-8">
                  Подсказки появятся здесь после генерации
                </p>
              ) : (
                <div className="space-y-4">
                  {suggestions.map((suggestion, index) => (
                    <div key={index} className="border rounded-lg p-4">
                      <div className="flex justify-between items-start mb-2">
                        <span className="text-sm font-medium text-primary">
                          {SUGGESTION_TYPES.find(t => t.type === suggestion.type)?.label}
                        </span>
                        <Button
                          size="sm"
                          variant="ghost"
                          onClick={() => copyToClipboard(suggestion.content)}
                        >
                          Копировать
                        </Button>
                      </div>
                      <div className="text-sm whitespace-pre-wrap">
                        {suggestion.content}
                      </div>
                      <div className="text-xs text-gray-500 mt-2">
                        {new Date(suggestion.metadata.timestamp).toLocaleString()}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Правая панель - Быстрые действия */}
        <div className="space-y-6">
          {/* Быстрые действия */}
          <Card>
            <CardHeader>
              <CardTitle>Быстрые действия</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
              <Button 
                variant="outline" 
                className="w-full justify-start"
                onClick={() => {
                  const dice = Math.floor(Math.random() * 20) + 1
                  alert(`🎲 Выпало: ${dice}`)
                }}
              >
                🎲 Бросить кости (d20)
              </Button>
              <Button 
                variant="outline" 
                className="w-full justify-start"
                onClick={() => window.open('/tables/tavern_names/random', '_blank')}
              >
                🏪 Случайная таверна
              </Button>
              <Button 
                variant="outline" 
                className="w-full justify-start"
                onClick={() => window.open('/tables/potions/random', '_blank')}
              >
                🧪 Случайное зелье
              </Button>
              <Button 
                variant="outline" 
                className="w-full justify-start"
                onClick={() => window.open('/tables/npcs/random', '_blank')}
              >
                🗣️ Случайный NPC
              </Button>
              <Button 
                variant="outline" 
                className="w-full justify-start"
                onClick={() => window.open('/tables/drinks/random', '_blank')}
              >
                🍺 Случайный напиток
              </Button>
            </CardContent>
          </Card>

          {/* Статистика сессии */}
          <Card>
            <CardHeader>
              <CardTitle>Статистика</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span>Этап:</span>
                  <span className="font-medium">
                    {SESSION_STAGES.find(s => s.value === currentStage)?.label}
                  </span>
                </div>
                <div className="flex justify-between">
                  <span>Подсказок:</span>
                  <span className="font-medium">{suggestions.length}</span>
                </div>
                <div className="flex justify-between">
                  <span>Время:</span>
                  <span className="font-medium">--:--</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  )
}
