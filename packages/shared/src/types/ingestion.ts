export interface Document {
  id: string
  title: string
  content: string
  type: DocumentType
  metadata: DocumentMetadata
  createdAt: Date
  updatedAt: Date
}

export enum DocumentType {
  PDF = 'PDF',
  MARKDOWN = 'MARKDOWN',
  TEXT = 'TEXT',
  JSON = 'JSON'
}

export interface DocumentMetadata {
  author?: string
  version?: string
  tags?: string[]
  summary?: string
  pageCount?: number
  fileSize?: number
  language?: string
}

export interface DocumentChunk {
  id: string
  documentId: string
  text: string
  embedding: number[]
  metadata: ChunkMetadata
  createdAt: Date
}

export interface ChunkMetadata {
  position: number
  type: 'paragraph' | 'section' | 'table' | 'list' | 'header'
  level?: number
  tags?: string[]
  summary?: string
}

export interface IngestionResult {
  documentId: string
  chunksCreated: number
  processingTime: number
  errors: string[]
  warnings: string[]
}

export interface ChunkingConfig {
  maxChunkSize: number
  overlapSize: number
  separator: string
  preserveHeaders: boolean
}

export interface ParsingResult {
  content: string
  metadata: DocumentMetadata
  chunks: string[]
}
