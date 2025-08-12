export interface TableColumn {
  name: string
  type: 'string' | 'number' | 'boolean' | 'date'
  required: boolean
  description?: string
}

export interface TableSchema {
  columns: TableColumn[]
  description?: string
}

export interface Table {
  id: string
  name: string
  schema: TableSchema
  rows: any[]
  updatedAt: Date
  createdAt: Date
}

export interface TableRow {
  [key: string]: any
}

// Предопределенные таблицы
export interface Drink {
  name: string
  region: string
  effect: string
  quirk: string
}

export interface TravelEvent {
  hook: string
  obstacle: string
  twist: string
}

export interface NPC {
  name: string
  role: string
  trait: string
  voice: string
  secret: string
}

export interface Potion {
  name: string
  rarity: string
  effect: string
  side_effect: string
}

export interface TavernName {
  prefix: string
  suffix: string
  vibe: string
}

export interface TableOperation {
  type: 'create' | 'update' | 'delete' | 'read'
  tableName: string
  data?: any
  filter?: any
}
