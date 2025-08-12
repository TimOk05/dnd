import { DocumentType, SUPPORTED_FORMATS, LIMITS } from '../types/ingestion'

/**
 * Валидация файла
 */
export function validateFile(file: File): { valid: boolean; error?: string } {
  // Проверка размера
  if (file.size > LIMITS.MAX_FILE_SIZE) {
    return {
      valid: false,
      error: 'Файл слишком большой. Максимальный размер: 10MB'
    }
  }

  // Проверка формата
  const extension = file.name.toLowerCase().substring(file.name.lastIndexOf('.'))
  const supportedExtensions = Object.values(SUPPORTED_FORMATS)
  
  if (!supportedExtensions.includes(extension)) {
    return {
      valid: false,
      error: `Неподдерживаемый формат файла. Поддерживаемые форматы: ${supportedExtensions.join(', ')}`
    }
  }

  return { valid: true }
}

/**
 * Определение типа документа по расширению
 */
export function getDocumentType(filename: string): DocumentType {
  const extension = filename.toLowerCase().substring(filename.lastIndexOf('.'))
  
  switch (extension) {
    case '.pdf':
      return DocumentType.PDF
    case '.md':
      return DocumentType.MARKDOWN
    case '.txt':
      return DocumentType.TEXT
    case '.json':
      return DocumentType.JSON
    default:
      return DocumentType.TEXT
  }
}

/**
 * Валидация схемы таблицы
 */
export function validateTableSchema(schema: any): { valid: boolean; error?: string } {
  if (!schema || typeof schema !== 'object') {
    return { valid: false, error: 'Схема должна быть объектом' }
  }

  if (!schema.columns || !Array.isArray(schema.columns)) {
    return { valid: false, error: 'Схема должна содержать массив columns' }
  }

  if (schema.columns.length === 0) {
    return { valid: false, error: 'Схема должна содержать хотя бы одну колонку' }
  }

  for (const column of schema.columns) {
    if (!column.name || typeof column.name !== 'string') {
      return { valid: false, error: 'Каждая колонка должна иметь имя' }
    }

    if (!column.type || !['string', 'number', 'boolean', 'date'].includes(column.type)) {
      return { valid: false, error: 'Неверный тип колонки' }
    }
  }

  return { valid: true }
}

/**
 * Валидация данных таблицы
 */
export function validateTableData(data: any[], schema: any): { valid: boolean; error?: string } {
  if (!Array.isArray(data)) {
    return { valid: false, error: 'Данные должны быть массивом' }
  }

  for (let i = 0; i < data.length; i++) {
    const row = data[i]
    if (typeof row !== 'object' || row === null) {
      return { valid: false, error: `Строка ${i + 1} должна быть объектом` }
    }

    for (const column of schema.columns) {
      if (column.required && !(column.name in row)) {
        return { valid: false, error: `Строка ${i + 1}: отсутствует обязательная колонка "${column.name}"` }
      }

      if (column.name in row) {
        const value = row[column.name]
        if (!validateColumnValue(value, column.type)) {
          return { valid: false, error: `Строка ${i + 1}: неверный тип для колонки "${column.name}"` }
        }
      }
    }
  }

  return { valid: true }
}

/**
 * Валидация значения колонки
 */
function validateColumnValue(value: any, type: string): boolean {
  switch (type) {
    case 'string':
      return typeof value === 'string'
    case 'number':
      return typeof value === 'number' && !isNaN(value)
    case 'boolean':
      return typeof value === 'boolean'
    case 'date':
      return value instanceof Date || (typeof value === 'string' && !isNaN(Date.parse(value)))
    default:
      return false
  }
}

/**
 * Валидация промпта
 */
export function validatePrompt(prompt: any): { valid: boolean; error?: string } {
  if (!prompt || typeof prompt !== 'object') {
    return { valid: false, error: 'Промпт должен быть объектом' }
  }

  if (!prompt.system || typeof prompt.system !== 'string') {
    return { valid: false, error: 'Промпт должен содержать system сообщение' }
  }

  if (!prompt.user || typeof prompt.user !== 'string') {
    return { valid: false, error: 'Промпт должен содержать user сообщение' }
  }

  if (prompt.system.length > 10000) {
    return { valid: false, error: 'System сообщение слишком длинное' }
  }

  if (prompt.user.length > 10000) {
    return { valid: false, error: 'User сообщение слишком длинное' }
  }

  return { valid: true }
}
