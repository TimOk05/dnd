import pdf from 'pdf-parse'
import { DocumentType, DocumentMetadata, ParsingResult } from 'dm-copilot-shared'

/**
 * Парсинг документа в зависимости от типа
 */
export async function parseDocument(
  buffer: Buffer,
  type: DocumentType,
  filename: string
): Promise<ParsingResult> {
  switch (type) {
    case DocumentType.PDF:
      return parsePDF(buffer, filename)
    case DocumentType.MARKDOWN:
      return parseMarkdown(buffer, filename)
    case DocumentType.TEXT:
      return parseText(buffer, filename)
    case DocumentType.JSON:
      return parseJSON(buffer, filename)
    default:
      return parseText(buffer, filename)
  }
}

/**
 * Парсинг PDF файла
 */
async function parsePDF(buffer: Buffer, filename: string): Promise<ParsingResult> {
  try {
    const data = await pdf(buffer)
    
    const metadata: DocumentMetadata = {
      pageCount: data.numpages,
      fileSize: buffer.length,
      language: 'ru', // TODO: определить язык
      summary: data.text.substring(0, 200) + '...'
    }

    return {
      content: data.text,
      metadata,
      chunks: [] // Чанки будут созданы позже
    }
  } catch (error) {
    throw new Error(`Ошибка парсинга PDF: ${error}`)
  }
}

/**
 * Парсинг Markdown файла
 */
function parseMarkdown(buffer: Buffer, filename: string): Promise<ParsingResult> {
  const content = buffer.toString('utf-8')
  
  const metadata: DocumentMetadata = {
    fileSize: buffer.length,
    language: 'ru',
    summary: content.substring(0, 200) + '...'
  }

  return Promise.resolve({
    content,
    metadata,
    chunks: []
  })
}

/**
 * Парсинг текстового файла
 */
function parseText(buffer: Buffer, filename: string): Promise<ParsingResult> {
  const content = buffer.toString('utf-8')
  
  const metadata: DocumentMetadata = {
    fileSize: buffer.length,
    language: 'ru',
    summary: content.substring(0, 200) + '...'
  }

  return Promise.resolve({
    content,
    metadata,
    chunks: []
  })
}

/**
 * Парсинг JSON файла
 */
function parseJSON(buffer: Buffer, filename: string): Promise<ParsingResult> {
  try {
    const jsonData = JSON.parse(buffer.toString('utf-8'))
    const content = JSON.stringify(jsonData, null, 2)
    
    const metadata: DocumentMetadata = {
      fileSize: buffer.length,
      language: 'ru',
      summary: 'JSON документ'
    }

    return Promise.resolve({
      content,
      metadata,
      chunks: []
    })
  } catch (error) {
    throw new Error(`Ошибка парсинга JSON: ${error}`)
  }
}

/**
 * Извлечение метаданных из текста
 */
export function extractMetadata(text: string): Partial<DocumentMetadata> {
  const metadata: Partial<DocumentMetadata> = {}

  // Поиск заголовка
  const titleMatch = text.match(/^#\s+(.+)$/m)
  if (titleMatch) {
    metadata.summary = titleMatch[1]
  }

  // Поиск автора
  const authorMatch = text.match(/автор[:\s]+(.+)/i)
  if (authorMatch) {
    metadata.author = authorMatch[1].trim()
  }

  // Поиск версии
  const versionMatch = text.match(/версия[:\s]+(.+)/i)
  if (versionMatch) {
    metadata.version = versionMatch[1].trim()
  }

  // Определение языка (простая эвристика)
  const russianChars = (text.match(/[а-яё]/gi) || []).length
  const englishChars = (text.match(/[a-z]/gi) || []).length
  
  if (russianChars > englishChars) {
    metadata.language = 'ru'
  } else {
    metadata.language = 'en'
  }

  return metadata
}
