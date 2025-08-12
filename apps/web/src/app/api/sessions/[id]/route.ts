import { NextRequest, NextResponse } from 'next/server'
import { getSession } from '@dm-copilot/database'

export async function GET(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  try {
    const session = await getSession(params.id)
    
    if (!session) {
      return NextResponse.json(
        { error: 'Сессия не найдена' },
        { status: 404 }
      )
    }

    return NextResponse.json({
      success: true,
      session
    })
  } catch (error) {
    console.error('Ошибка получения сессии:', error)
    return NextResponse.json(
      { error: 'Ошибка получения сессии' },
      { status: 500 }
    )
  }
}
