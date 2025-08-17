'use client'

import { useState } from 'react'
import { useRouter, useSearchParams } from 'next/navigation'
import SessionRunner from '@/components/SessionRunner'
import { Button } from '@/components/ui/Button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card'
import { Input } from '@/components/ui/Input'

export default function SessionPage() {
  const router = useRouter()
  const searchParams = useSearchParams()
  const sessionId = searchParams.get('id')
  
  const [isCreating, setIsCreating] = useState(!sessionId)
  const [sessionTitle, setSessionTitle] = useState('')
  const [isLoading, setIsLoading] = useState(false)

  const createSession = async () => {
    if (!sessionTitle.trim()) return

    setIsLoading(true)
    try {
      const response = await fetch('/api/sessions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          title: sessionTitle,
          mode: 'MANUAL',
          stage: 'HOOK'
        })
      })

      if (response.ok) {
        const session = await response.json()
        router.push(`/session?id=${session.id}`)
      }
    } catch (error) {
      console.error('Ошибка создания сессии:', error)
    } finally {
      setIsLoading(false)
    }
  }

  if (sessionId) {
    return <SessionRunner sessionId={sessionId} />
  }

  return (
    <div className="max-w-2xl mx-auto p-6">
      <div className="text-center mb-8">
        <h1 className="text-3xl font-bold text-primary mb-2">Новая сессия D&D</h1>
        <p className="text-gray-600">Создайте новую сессию для начала приключения</p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Создание сессии</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div>
            <label htmlFor="sessionTitle" className="block text-sm font-medium mb-2">
              Название сессии
            </label>
            <Input
              id="sessionTitle"
              type="text"
              placeholder="Например: Потерянный храм Эльдориана"
              value={sessionTitle}
              onChange={(e) => setSessionTitle(e.target.value)}
              onKeyPress={(e) => e.key === 'Enter' && createSession()}
            />
          </div>

          <div className="flex gap-3">
            <Button
              onClick={createSession}
              disabled={!sessionTitle.trim() || isLoading}
              className="flex-1"
              variant="primary"
            >
              {isLoading ? 'Создаю...' : 'Создать сессию'}
            </Button>
            <Button
              onClick={() => router.push('/')}
              variant="outline"
            >
              Отмена
            </Button>
          </div>
        </CardContent>
      </Card>

      <div className="mt-8 text-center">
        <p className="text-sm text-gray-500">
          После создания сессии вы сможете управлять этапами, делать заметки и получать AI подсказки
        </p>
      </div>
    </div>
  )
}
