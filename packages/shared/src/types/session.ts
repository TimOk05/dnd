export enum SessionMode {
  MANUAL = 'MANUAL',
  LIVE = 'LIVE'
}

export enum SessionStage {
  HOOK = 'HOOK',
  INTRO = 'INTRO',
  TRAVEL = 'TRAVEL',
  DUNGEON = 'DUNGEON',
  ENCOUNTER = 'ENCOUNTER',
  ROLEPLAY = 'ROLEPLAY',
  CLIMAX = 'CLIMAX',
  AFTERMATH = 'AFTERMATH'
}

export enum EventType {
  USER = 'USER',
  STT = 'STT',
  SYSTEM = 'SYSTEM',
  AI = 'AI'
}

export interface Session {
  id: string
  title: string
  mode: SessionMode
  stage: SessionStage
  notes?: string
  createdAt: Date
  updatedAt: Date
  events?: Event[]
}

export interface Event {
  id: string
  sessionId: string
  type: EventType
  payload: any
  createdAt: Date
}

export interface SessionStats {
  totalEvents: number
  userEvents: number
  aiEvents: number
  sttEvents: number
  systemEvents: number
  duration: number
}

export interface SessionContext {
  session: Session
  recentEvents: Event[]
  currentStage: SessionStage
  notes: string
  relevantChunks?: string[]
  tableData?: any[]
}
