import { PrismaClient, Document, Chunk } from '@prisma/client'
import { createChunkWithEmbedding } from './vector'

const prisma = new PrismaClient()

/**
 * Создание документа
 */
export async function createDocument(data: {
  title: string
  content: string
  type: string
  metadata: any
}): Promise<Document> {
  return prisma.document.create({
    data: {
      title: data.title,
      meta: data.metadata
    }
  })
}

/**
 * Получение документа по ID
 */
export async function getDocument(id: string): Promise<Document | null> {
  return prisma.document.findUnique({
    where: { id },
    include: {
      chunks: {
        orderBy: { createdAt: 'asc' }
      }
    }
  })
}

/**
 * Получение всех документов
 */
export async function getAllDocuments(): Promise<Document[]> {
  return prisma.document.findMany({
    orderBy: { createdAt: 'desc' },
    include: {
      _count: {
        select: { chunks: true }
      }
    }
  })
}

/**
 * Создание чанков с эмбеддингами
 */
export async function createChunksBatch(
  documentId: string,
  chunks: Array<{ text: string; meta?: any }>
): Promise<void> {
  for (const chunk of chunks) {
    await createChunkWithEmbedding(documentId, chunk.text, chunk.meta || {})
  }
}

/**
 * Получение чанков документа
 */
export async function getDocumentChunks(documentId: string): Promise<Chunk[]> {
  return prisma.chunk.findMany({
    where: { documentId },
    orderBy: { createdAt: 'asc' }
  })
}

/**
 * Удаление документа и всех его чанков
 */
export async function deleteDocument(id: string): Promise<Document> {
  return prisma.document.delete({
    where: { id }
  })
}

/**
 * Обновление документа
 */
export async function updateDocument(
  id: string,
  data: Partial<Pick<Document, 'title' | 'meta'>>
): Promise<Document> {
  return prisma.document.update({
    where: { id },
    data
  })
}

/**
 * Получение статистики документов
 */
export async function getDocumentsStats() {
  const [totalDocuments, totalChunks] = await Promise.all([
    prisma.document.count(),
    prisma.chunk.count()
  ])

  return {
    totalDocuments,
    totalChunks,
    averageChunksPerDocument: totalDocuments > 0 ? totalChunks / totalDocuments : 0
  }
}
