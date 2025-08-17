import { PrismaClient, Session, Event, SessionMode, SessionStage, EventType } from '@prisma/client'

const prisma = new PrismaClient()

/**
 * Создание новой сессии
 */
export async function createSession(
  title: string,
  mode: SessionMode = 'MANUAL',
  stage: SessionStage = 'HOOK',
  notes?: string
): Promise<Session> {
  return prisma.session.create({
    data: {
      title,
      mode,
      stage,
      notes
    }
  })
}

/**
 * Получение сессии по ID
 */
export async function getSession(id: string): Promise<Session | null> {
  return prisma.session.findUnique({
    where: { id },
    include: {
      events: {
        orderBy: { createdAt: 'asc' }
      }
    }
  })
}

/**
 * Получение всех сессий
 */
export async function getAllSessions(): Promise<Session[]> {
  return prisma.session.findMany({
    orderBy: { createdAt: 'desc' },
    include: {
      events: {
        orderBy: { createdAt: 'desc' },
        take: 1
      }
    }
  })
}

/**
 * Обновление этапа сессии
 */
export async function updateSessionStage(
  id: string,
  stage: SessionStage
): Promise<Session> {
  return prisma.session.update({
    where: { id },
    data: { stage }
  })
}

/**
 * Обновление заметок сессии
 */
export async function updateSessionNotes(
  id: string,
  notes: string
): Promise<Session> {
  return prisma.session.update({
    where: { id },
    data: { notes }
  })
}

/**
 * Создание события в сессии
 */
export async function createSessionEvent(
  sessionId: string,
  type: EventType,
  payload: any
): Promise<Event> {
  return prisma.event.create({
    data: {
      sessionId,
      type,
      payload
    }
  })
}

/**
 * Получение событий сессии
 */
export async function getSessionEvents(
  sessionId: string,
  limit?: number
): Promise<Event[]> {
  return prisma.event.findMany({
    where: { sessionId },
    orderBy: { createdAt: 'desc' },
    take: limit
  })
}

/**
 * Получение последних событий сессии
 */
export async function getRecentSessionEvents(
  sessionId: string,
  count: number = 10
): Promise<Event[]> {
  return prisma.event.findMany({
    where: { sessionId },
    orderBy: { createdAt: 'desc' },
    take: count
  })
}

/**
 * Удаление сессии и всех её событий
 */
export async function deleteSession(id: string): Promise<Session> {
  return prisma.session.delete({
    where: { id }
  })
}

/**
 * Получение статистики сессии
 */
export async function getSessionStats(sessionId: string) {
  const events = await prisma.event.findMany({
    where: { sessionId }
  })

  const stats = {
    totalEvents: events.length,
    userEvents: events.filter(e => e.type === 'USER').length,
    aiEvents: events.filter(e => e.type === 'AI').length,
    sttEvents: events.filter(e => e.type === 'STT').length,
    systemEvents: events.filter(e => e.type === 'SYSTEM').length,
    duration: 0
  }

  if (events.length > 1) {
    const firstEvent = events[0]
    const lastEvent = events[events.length - 1]
    stats.duration = lastEvent.createdAt.getTime() - firstEvent.createdAt.getTime()
  }

  return stats
}

/**
 * Экспорт сессии в JSON
 */
export async function exportSession(sessionId: string) {
  const session = await getSession(sessionId)
  if (!session) {
    throw new Error('Сессия не найдена')
  }

  return {
    id: session.id,
    title: session.title,
    mode: session.mode,
    stage: session.stage,
    notes: session.notes,
    createdAt: session.createdAt,
    updatedAt: session.updatedAt,
    events: session.events
  }
}

/**
 * Импорт сессии из JSON
 */
export async function importSession(sessionData: any): Promise<Session> {
  const { events, ...sessionInfo } = sessionData

  const session = await prisma.session.create({
    data: {
      title: sessionInfo.title,
      mode: sessionInfo.mode as SessionMode,
      stage: sessionInfo.stage as SessionStage,
      notes: sessionInfo.notes
    }
  })

  if (events && events.length > 0) {
    await prisma.event.createMany({
      data: events.map((event: any) => ({
        sessionId: session.id,
        type: event.type as EventType,
        payload: event.payload
      }))
    })
  }

  return session
}
