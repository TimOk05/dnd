import { NextRequest, NextResponse } from 'next/server'
import { updateSessionNotes } from '@dm-copilot/database'

export async function PUT(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  try {
    const body = await request.json()
    const { notes } = body

    const session = await updateSessionNotes(params.id, notes || '')

    return NextResponse.json({
      success: true,
      session
    })
  } catch (error) {
    console.error('Ошибка обновления заметок сессии:', error)
    return NextResponse.json(
      { error: 'Ошибка обновления заметок сессии' },
      { status: 500 }
    )
  }
}
