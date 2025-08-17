import { NextRequest, NextResponse } from 'next/server'
import { 
  getTable, 
  getAllTables, 
  createTable, 
  updateTable, 
  deleteTable,
  addTableRow,
  updateTableRow,
  deleteTableRow,
  getRandomTableRow
} from '@/lib/database'

// GET /api/tables - получить все таблицы
// GET /api/tables/[name] - получить конкретную таблицу
export async function GET(
  request: NextRequest,
  { params }: { params: { name: string } }
) {
  try {
    const { searchParams } = new URL(request.url)
    const action = searchParams.get('action')

    // Если запрос на все таблицы
    if (!params.name) {
      const tables = await getAllTables()
      return NextResponse.json(tables)
    }

    // Если запрос на случайную строку
    if (action === 'random') {
      const randomRow = await getRandomTableRow(params.name)
      return NextResponse.json(randomRow)
    }

    // Получение конкретной таблицы
    const table = await getTable(params.name)
    if (!table) {
      return NextResponse.json(
        { error: 'Таблица не найдена' },
        { status: 404 }
      )
    }

    return NextResponse.json(table)

  } catch (error) {
    console.error('Ошибка получения таблицы:', error)
    return NextResponse.json(
      { error: 'Ошибка получения таблицы' },
      { status: 500 }
    )
  }
}

// POST /api/tables - создать новую таблицу
// POST /api/tables/[name] - добавить строку в таблицу
export async function POST(
  request: NextRequest,
  { params }: { params: { name: string } }
) {
  try {
    const body = await request.json()

    // Создание новой таблицы
    if (!params.name) {
      const { name, schema, rows } = body
      const table = await createTable(name, schema, rows || [])
      return NextResponse.json(table)
    }

    // Добавление строки в существующую таблицу
    const table = await addTableRow(params.name, body)
    return NextResponse.json(table)

  } catch (error) {
    console.error('Ошибка создания/обновления таблицы:', error)
    return NextResponse.json(
      { error: 'Ошибка создания/обновления таблицы' },
      { status: 500 }
    )
  }
}

// PUT /api/tables/[name] - обновить таблицу или строку
export async function PUT(
  request: NextRequest,
  { params }: { params: { name: string } }
) {
  try {
    const body = await request.json()
    const { searchParams } = new URL(request.url)
    const rowIndex = searchParams.get('rowIndex')

    if (rowIndex !== null) {
      // Обновление конкретной строки
      const index = parseInt(rowIndex)
      const table = await updateTableRow(params.name, index, body)
      return NextResponse.json(table)
    } else {
      // Обновление всей таблицы
      const table = await updateTable(params.name, body)
      return NextResponse.json(table)
    }

  } catch (error) {
    console.error('Ошибка обновления таблицы:', error)
    return NextResponse.json(
      { error: 'Ошибка обновления таблицы' },
      { status: 500 }
    )
  }
}

// DELETE /api/tables/[name] - удалить таблицу или строку
export async function DELETE(
  request: NextRequest,
  { params }: { params: { name: string } }
) {
  try {
    const { searchParams } = new URL(request.url)
    const rowIndex = searchParams.get('rowIndex')

    if (rowIndex !== null) {
      // Удаление конкретной строки
      const index = parseInt(rowIndex)
      const table = await deleteTableRow(params.name, index)
      return NextResponse.json(table)
    } else {
      // Удаление всей таблицы
      const table = await deleteTable(params.name)
      return NextResponse.json(table)
    }

  } catch (error) {
    console.error('Ошибка удаления таблицы:', error)
    return NextResponse.json(
      { error: 'Ошибка удаления таблицы' },
      { status: 500 }
    )
  }
}
