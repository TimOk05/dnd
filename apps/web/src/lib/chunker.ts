import { ChunkingConfig } from 'dm-copilot-shared'

/**
 * Разбиение текста на чанки
 */
export function chunkText(
  text: string,
  config: Partial<ChunkingConfig> = {}
): string[] {
  const {
    maxChunkSize = 1000,
    overlapSize = 200,
    separator = '\n\n',
    preserveHeaders = true
  } = config

  // Если текст меньше максимального размера чанка, возвращаем его целиком
  if (text.length <= maxChunkSize) {
    return [text]
  }

  const chunks: string[] = []
  let currentChunk = ''
  let currentPosition = 0

  // Разбиваем текст по сепаратору
  const segments = text.split(separator)

  for (const segment of segments) {
    // Если добавление сегмента превысит лимит
    if (currentChunk.length + segment.length > maxChunkSize) {
      // Если текущий чанк не пустой, сохраняем его
      if (currentChunk.trim()) {
        chunks.push(currentChunk.trim())
      }

      // Если сегмент сам по себе больше лимита, разбиваем его
      if (segment.length > maxChunkSize) {
        const subChunks = splitLargeSegment(segment, maxChunkSize, overlapSize)
        chunks.push(...subChunks)
        currentChunk = ''
      } else {
        // Начинаем новый чанк с перекрытием
        const overlap = getOverlap(currentChunk, overlapSize)
        currentChunk = overlap + segment
      }
    } else {
      // Добавляем сегмент к текущему чанку
      if (currentChunk) {
        currentChunk += separator + segment
      } else {
        currentChunk = segment
      }
    }
  }

  // Добавляем последний чанк
  if (currentChunk.trim()) {
    chunks.push(currentChunk.trim())
  }

  return chunks.filter(chunk => chunk.length > 0)
}

/**
 * Разбиение большого сегмента на части
 */
function splitLargeSegment(
  segment: string,
  maxChunkSize: number,
  overlapSize: number
): string[] {
  const chunks: string[] = []
  let start = 0

  while (start < segment.length) {
    const end = Math.min(start + maxChunkSize, segment.length)
    let chunk = segment.substring(start, end)

    // Если это не последний чанк, пытаемся разбить по словам
    if (end < segment.length) {
      const lastSpace = chunk.lastIndexOf(' ')
      if (lastSpace > maxChunkSize * 0.8) {
        chunk = chunk.substring(0, lastSpace)
        start = start + lastSpace + 1
      } else {
        start = end
      }
    } else {
      start = end
    }

    chunks.push(chunk)
  }

  return chunks
}

/**
 * Получение перекрытия между чанками
 */
function getOverlap(text: string, overlapSize: number): string {
  if (text.length <= overlapSize) {
    return text
  }

  const overlap = text.substring(text.length - overlapSize)
  
  // Ищем последний полный сегмент в перекрытии
  const lastSegment = overlap.split('\n\n').pop()
  if (lastSegment && lastSegment.length > overlapSize * 0.5) {
    return lastSegment
  }

  return overlap
}

/**
 * Разбиение текста по заголовкам (для Markdown)
 */
export function chunkByHeaders(text: string, maxChunkSize: number = 1000): string[] {
  const chunks: string[] = []
  const lines = text.split('\n')
  let currentChunk = ''
  let currentHeader = ''

  for (const line of lines) {
    // Проверяем, является ли строка заголовком
    const headerMatch = line.match(/^(#{1,6})\s+(.+)$/)
    
    if (headerMatch) {
      // Если текущий чанк не пустой, сохраняем его
      if (currentChunk.trim()) {
        chunks.push(currentChunk.trim())
      }

      // Начинаем новый чанк с заголовком
      currentHeader = line
      currentChunk = line + '\n'
    } else {
      // Добавляем строку к текущему чанку
      if (currentChunk.length + line.length > maxChunkSize) {
        // Если чанк стал слишком большим, сохраняем его
        if (currentChunk.trim()) {
          chunks.push(currentChunk.trim())
        }
        
        // Начинаем новый чанк
        currentChunk = currentHeader + '\n' + line + '\n'
      } else {
        currentChunk += line + '\n'
      }
    }
  }

  // Добавляем последний чанк
  if (currentChunk.trim()) {
    chunks.push(currentChunk.trim())
  }

  return chunks.filter(chunk => chunk.length > 0)
}

/**
 * Разбиение текста по предложениям
 */
export function chunkBySentences(text: string, maxChunkSize: number = 1000): string[] {
  const sentences = text.match(/[^.!?]+[.!?]+/g) || [text]
  const chunks: string[] = []
  let currentChunk = ''

  for (const sentence of sentences) {
    if (currentChunk.length + sentence.length > maxChunkSize) {
      if (currentChunk.trim()) {
        chunks.push(currentChunk.trim())
      }
      currentChunk = sentence
    } else {
      currentChunk += sentence
    }
  }

  if (currentChunk.trim()) {
    chunks.push(currentChunk.trim())
  }

  return chunks.filter(chunk => chunk.length > 0)
}
