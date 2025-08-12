import { NextRequest, NextResponse } from 'next/server'
import { updateSessionStage } from '@dm-copilot/database'
import { SessionStage } from '@dm-copilot/shared'

export async function PUT(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  try {
    const body = await request.json()
    const { stage } = body

    if (!stage) {
      return NextResponse.json(
        { error: 'Этап сессии обязателен' },
        { status: 400 }
      )
    }

    const session = await updateSessionStage(params.id, stage as SessionStage)

    return NextResponse.json({
      success: true,
      session
    })
  } catch (error) {
    console.error('Ошибка обновления этапа сессии:', error)
    return NextResponse.json(
      { error: 'Ошибка обновления этапа сессии' },
      { status: 500 }
    )
  }
}
