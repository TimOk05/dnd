import { PrismaClient } from '@prisma/client'
import { pipeline } from '@xenova/transformers'

const prisma = new PrismaClient()

// Кэш для модели трансформеров
let embeddingPipeline: any = null

/**
 * Инициализация модели для эмбеддингов
 */
async function getEmbeddingPipeline() {
  if (!embeddingPipeline) {
    embeddingPipeline = await pipeline('feature-extraction', 'Xenova/all-MiniLM-L6-v2')
  }
  return embeddingPipeline
}

/**
 * Генерация эмбеддинга для текста
 */
export async function generateEmbedding(text: string): Promise<number[]> {
  const pipe = await getEmbeddingPipeline()
  const result = await pipe(text, { pooling: 'mean', normalize: true })
  return Array.from(result.data)
}

/**
 * Поиск похожих чанков по вектору
 */
export async function findSimilarChunks(
  embedding: number[],
  limit: number = 5,
  threshold: number = 0.7
) {
  const chunks = await prisma.$queryRaw`
    SELECT 
      c.id,
      c.text,
      c.meta,
      d.title as document_title,
      1 - (c.embedding <=> ${embedding}::vector) as similarity
    FROM chunks c
    JOIN documents d ON c.document_id = d.id
    WHERE 1 - (c.embedding <=> ${embedding}::vector) > ${threshold}
    ORDER BY c.embedding <=> ${embedding}::vector
    LIMIT ${limit}
  `

  return chunks
}

/**
 * Создание чанка с эмбеддингом
 */
export async function createChunkWithEmbedding(
  documentId: string,
  text: string,
  meta: any = {}
) {
  const embedding = await generateEmbedding(text)
  
  return prisma.chunk.create({
    data: {
      documentId,
      text,
      embedding: embedding as any, // Prisma автоматически конвертирует в vector
      meta
    }
  })
}

/**
 * Пакетное создание чанков с эмбеддингами
 */
export async function createChunksBatch(
  documentId: string,
  chunks: Array<{ text: string; meta?: any }>
) {
  const chunksWithEmbeddings = await Promise.all(
    chunks.map(async (chunk) => {
      const embedding = await generateEmbedding(chunk.text)
      return {
        documentId,
        text: chunk.text,
        embedding: embedding as any,
        meta: chunk.meta || {}
      }
    })
  )

  return prisma.chunk.createMany({
    data: chunksWithEmbeddings
  })
}
