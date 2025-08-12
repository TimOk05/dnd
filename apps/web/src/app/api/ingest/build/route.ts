import { NextRequest, NextResponse } from 'next/server'
import { readFile } from 'fs/promises'
import { join } from 'path'
import { parseDocument } from '@/lib/parser'
import { chunkText } from '@/lib/chunker'
import { createDocument, createChunksBatch } from '@/lib/database'

export async function POST(request: NextRequest) {
  try {
    const { fileName, documentType, originalName } = await request.json()

    if (!fileName) {
      return NextResponse.json(
        { error: 'Имя файла не указано' },
        { status: 400 }
      )
    }

    // Чтение файла
    const filePath = join(process.cwd(), 'uploads', fileName)
    const fileBuffer = await readFile(filePath)

    // Парсинг документа
    const parseResult = await parseDocument(fileBuffer, documentType, originalName)

    // Создание документа в БД
    const document = await createDocument({
      title: originalName,
      content: parseResult.content,
      type: documentType,
      metadata: parseResult.metadata
    })

    // Разбиение на чанки
    const chunks = chunkText(parseResult.content, {
      maxChunkSize: 1000,
      overlapSize: 200,
      separator: '\n\n'
    })

    // Создание чанков с эмбеддингами
    const chunksWithMeta = chunks.map((text, index) => ({
      text,
      meta: {
        position: index,
        type: 'paragraph',
        documentId: document.id
      }
    }))

    await createChunksBatch(document.id, chunksWithMeta)

    return NextResponse.json({
      success: true,
      documentId: document.id,
      chunksCreated: chunks.length,
      processingTime: Date.now() - Date.now() // TODO: добавить реальное время
    })

  } catch (error) {
    console.error('Ошибка обработки документа:', error)
    return NextResponse.json(
      { error: 'Ошибка обработки документа' },
      { status: 500 }
    )
  }
}
