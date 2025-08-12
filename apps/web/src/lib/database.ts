// Реальные функции из packages/database
import { 
  createDocument as dbCreateDocument,
  createChunksBatch as dbCreateChunksBatch,
  getTable as dbGetTable,
  getAllTables as dbGetAllTables,
  createTable as dbCreateTable,
  updateTable as dbUpdateTable,
  deleteTable as dbDeleteTable,
  addTableRow as dbAddTableRow,
  updateTableRow as dbUpdateTableRow,
  deleteTableRow as dbDeleteTableRow,
  getRandomTableRow as dbGetRandomTableRow,
  findSimilarChunks
} from '@dm-copilot/database'

import { Document, DocumentType, DocumentMetadata } from '@dm-copilot/shared'

/**
 * Создание документа
 */
export async function createDocument(data: {
  title: string
  content: string
  type: DocumentType
  metadata: DocumentMetadata
}): Promise<Document> {
  return await dbCreateDocument({
    title: data.title,
    content: data.content,
    type: data.type,
    metadata: data.metadata
  })
}

/**
 * Создание чанков с эмбеддингами
 */
export async function createChunksBatch(
  documentId: string,
  chunks: Array<{ text: string; meta?: any }>
): Promise<void> {
  return await dbCreateChunksBatch(documentId, chunks)
}

/**
 * Получение таблицы по имени
 */
export async function getTable(name: string): Promise<any> {
  return await dbGetTable(name)
}

/**
 * Получение всех таблиц
 */
export async function getAllTables(): Promise<any[]> {
  return await dbGetAllTables()
}

/**
 * Создание новой таблицы
 */
export async function createTable(
  name: string,
  schema: any,
  rows: any[] = []
): Promise<any> {
  return await dbCreateTable({ name, schema, rows })
}

/**
 * Обновление таблицы
 */
export async function updateTable(
  name: string,
  data: any
): Promise<any> {
  return await dbUpdateTable(name, data)
}

/**
 * Удаление таблицы
 */
export async function deleteTable(name: string): Promise<any> {
  return await dbDeleteTable(name)
}

/**
 * Добавление строки в таблицу
 */
export async function addTableRow(name: string, row: any): Promise<any> {
  return await dbAddTableRow(name, row)
}

/**
 * Обновление строки в таблице
 */
export async function updateTableRow(
  name: string,
  index: number,
  row: any
): Promise<any> {
  return await dbUpdateTableRow(name, index.toString(), row)
}

/**
 * Удаление строки из таблицы
 */
export async function deleteTableRow(name: string, index: number): Promise<any> {
  return await dbDeleteTableRow(name, index.toString())
}

/**
 * Случайная выборка из таблицы
 */
export async function getRandomTableRow(name: string): Promise<any | null> {
  return await dbGetRandomTableRow(name)
}

/**
 * Поиск похожих чанков по векторному поиску
 */
export async function searchSimilarChunks(query: string, limit: number = 5) {
  const { generateEmbedding } = await import('@dm-copilot/database')
  const embedding = await generateEmbedding(query)
  return await findSimilarChunks(embedding, limit)
}
