import { PrismaClient, Table } from '@prisma/client'

const prisma = new PrismaClient()

/**
 * Получение таблицы по имени
 */
export async function getTable(name: string): Promise<Table | null> {
  return prisma.table.findUnique({
    where: { name }
  })
}

/**
 * Получение всех таблиц
 */
export async function getAllTables(): Promise<Table[]> {
  return prisma.table.findMany({
    orderBy: { name: 'asc' }
  })
}

/**
 * Создание новой таблицы
 */
export async function createTable(
  name: string,
  schema: any,
  rows: any[] = []
): Promise<Table> {
  return prisma.table.create({
    data: {
      name,
      schema,
      rows
    }
  })
}

/**
 * Обновление таблицы
 */
export async function updateTable(
  name: string,
  data: Partial<Pick<Table, 'schema' | 'rows'>>
): Promise<Table> {
  return prisma.table.update({
    where: { name },
    data
  })
}

/**
 * Удаление таблицы
 */
export async function deleteTable(name: string): Promise<Table> {
  return prisma.table.delete({
    where: { name }
  })
}

/**
 * Добавление строки в таблицу
 */
export async function addTableRow(name: string, row: any): Promise<Table> {
  const table = await getTable(name)
  if (!table) {
    throw new Error(`Таблица ${name} не найдена`)
  }

  const updatedRows = [...table.rows, row]
  
  return prisma.table.update({
    where: { name },
    data: { rows: updatedRows }
  })
}

/**
 * Обновление строки в таблице
 */
export async function updateTableRow(
  name: string,
  index: number,
  row: any
): Promise<Table> {
  const table = await getTable(name)
  if (!table) {
    throw new Error(`Таблица ${name} не найдена`)
  }

  const updatedRows = [...table.rows]
  updatedRows[index] = row

  return prisma.table.update({
    where: { name },
    data: { rows: updatedRows }
  })
}

/**
 * Удаление строки из таблицы
 */
export async function deleteTableRow(name: string, index: number): Promise<Table> {
  const table = await getTable(name)
  if (!table) {
    throw new Error(`Таблица ${name} не найдена`)
  }

  const updatedRows = table.rows.filter((_, i) => i !== index)

  return prisma.table.update({
    where: { name },
    data: { rows: updatedRows }
  })
}

/**
 * Случайная выборка из таблицы
 */
export async function getRandomTableRow(name: string): Promise<any | null> {
  const table = await getTable(name)
  if (!table || table.rows.length === 0) {
    return null
  }

  const randomIndex = Math.floor(Math.random() * table.rows.length)
  return table.rows[randomIndex]
}

/**
 * Фильтрация строк таблицы
 */
export async function filterTableRows(
  name: string,
  filterFn: (row: any) => boolean
): Promise<any[]> {
  const table = await getTable(name)
  if (!table) {
    return []
  }

  return table.rows.filter(filterFn)
}
