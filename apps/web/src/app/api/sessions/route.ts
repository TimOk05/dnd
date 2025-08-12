import { NextRequest, NextResponse } from 'next/server'
import { createSession, getAllSessions } from '@dm-copilot/database'
import { SessionMode, SessionStage } from '@dm-copilot/shared'

export async function POST(request: NextRequest) {
  try {
    const body = await request.json()
    const { title, mode = 'MANUAL', stage = 'HOOK', notes } = body

    if (!title) {
      return NextResponse.json(
        { error: 'Название сессии обязательно' },
        { status: 400 }
      )
    }

    const session = await createSession(
      title,
      mode as SessionMode,
      stage as SessionStage,
      notes
    )

    return NextResponse.json({
      success: true,
      session
    })
  } catch (error) {
    console.error('Ошибка создания сессии:', error)
    return NextResponse.json(
      { error: 'Ошибка создания сессии' },
      { status: 500 }
    )
  }
}

export async function GET() {
  try {
    const sessions = await getAllSessions()
    
    return NextResponse.json({
      success: true,
      sessions
    })
  } catch (error) {
    console.error('Ошибка получения сессий:', error)
    return NextResponse.json(
      { error: 'Ошибка получения сессий' },
      { status: 500 }
    )
  }
}
