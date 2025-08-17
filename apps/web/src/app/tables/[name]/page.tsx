'use client'

import { useState, useEffect } from 'react'
import { useParams, useRouter } from 'next/navigation'
import { Button } from '@/components/ui/Button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card'
import { Input } from '@/components/ui/Input'
import { 
  ArrowLeft, 
  Plus, 
  Edit, 
  Trash2, 
  Save, 
  X,
  Download,
  Upload
} from 'lucide-react'

interface TableRow {
  id?: string
  [key: string]: any
}

interface TableSchema {
  columns: Array<{
    name: string
    type: string
    required?: boolean
  }>
}

export default function TableEditPage() {
  const params = useParams()
  const router = useRouter()
  const tableName = params.name as string

  const [table, setTable] = useState<any>(null)
  const [rows, setRows] = useState<TableRow[]>([])
  const [schema, setSchema] = useState<TableSchema>({ columns: [] })
  const [isLoading, setIsLoading] = useState(true)
  const [editingRow, setEditingRow] = useState<number | null>(null)
  const [newRow, setNewRow] = useState<TableRow>({})
  const [isAddingRow, setIsAddingRow] = useState(false)

  useEffect(() => {
    loadTable()
  }, [tableName])

  const loadTable = async () => {
    try {
      const response = await fetch(`/api/tables/${tableName}`)
      if (response.ok) {
        const data = await response.json()
        setTable(data.table)
        setRows(data.table.rows || [])
        setSchema(data.table.schema || { columns: [] })
      }
    } catch (error) {
      console.error('Ошибка загрузки таблицы:', error)
    } finally {
      setIsLoading(false)
    }
  }

  const handleAddRow = () => {
    setIsAddingRow(true)
    setNewRow({})
  }

  const handleSaveNewRow = async () => {
    try {
      const response = await fetch(`/api/tables/${tableName}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ row: newRow })
      })

      if (response.ok) {
        setRows([...rows, newRow])
        setNewRow({})
        setIsAddingRow(false)
        await loadTable() // Перезагружаем для получения актуальных данных
      }
    } catch (error) {
      console.error('Ошибка добавления строки:', error)
    }
  }

  const handleEditRow = (index: number) => {
    setEditingRow(index)
  }

  const handleSaveEdit = async (index: number) => {
    try {
      const updatedRows = [...rows]
      updatedRows[index] = { ...updatedRows[index] }

      const response = await fetch(`/api/tables/${tableName}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
          rows: updatedRows 
        })
      })

      if (response.ok) {
        setRows(updatedRows)
        setEditingRow(null)
        await loadTable()
      }
    } catch (error) {
      console.error('Ошибка сохранения:', error)
    }
  }

  const handleDeleteRow = async (index: number) => {
    if (!confirm('Удалить эту запись?')) return

    try {
      const response = await fetch(`/api/tables/${tableName}`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ index })
      })

      if (response.ok) {
        const updatedRows = rows.filter((_, i) => i !== index)
        setRows(updatedRows)
        await loadTable()
      }
    } catch (error) {
      console.error('Ошибка удаления:', error)
    }
  }

  const handleExportCSV = () => {
    if (rows.length === 0) return

    const headers = schema.columns.map(col => col.name).join(',')
    const csvContent = [
      headers,
      ...rows.map(row => 
        schema.columns.map(col => 
          JSON.stringify(row[col.name] || '')
        ).join(',')
      )
    ].join('\n')

    const blob = new Blob([csvContent], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${tableName}.csv`
    a.click()
    window.URL.revokeObjectURL(url)
  }

  const getTableDisplayName = (name: string) => {
    const names: Record<string, string> = {
      drinks: 'Напитки',
      npcs: 'NPC',
      potions: 'Зелья',
      events_travel: 'События в пути',
      tavern_names: 'Названия таверн'
    }
    return names[name] || name
  }

  if (isLoading) {
    return (
      <div className="max-w-6xl mx-auto p-6">
        <div className="text-center">Загрузка...</div>
      </div>
    )
  }

  return (
    <div className="max-w-6xl mx-auto p-6">
      {/* Заголовок */}
      <div className="flex justify-between items-center mb-8">
        <div className="flex items-center">
          <Button 
            variant="outline" 
            onClick={() => router.push('/tables')}
            className="mr-4"
          >
            <ArrowLeft className="w-4 h-4 mr-2" />
            Назад
          </Button>
          <div>
            <h1 className="text-3xl font-bold text-primary">
              {getTableDisplayName(tableName)}
            </h1>
            <p className="text-gray-600">
              {rows.length} записей • {schema.columns.length} полей
            </p>
          </div>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={handleExportCSV}>
            <Download className="w-4 h-4 mr-2" />
            Экспорт CSV
          </Button>
          <Button variant="primary" onClick={handleAddRow}>
            <Plus className="w-4 h-4 mr-2" />
            Добавить запись
          </Button>
        </div>
      </div>

      {/* Таблица */}
      <Card>
        <CardHeader>
          <CardTitle>Записи таблицы</CardTitle>
        </CardHeader>
        <CardContent>
          {rows.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              <p>Записей пока нет</p>
              <Button 
                variant="primary" 
                onClick={handleAddRow}
                className="mt-4"
              >
                <Plus className="w-4 h-4 mr-2" />
                Добавить первую запись
              </Button>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full border-collapse">
                <thead>
                  <tr className="border-b">
                    {schema.columns.map((column) => (
                      <th key={column.name} className="text-left p-3 font-medium">
                        {column.name}
                      </th>
                    ))}
                    <th className="text-right p-3 font-medium">Действия</th>
                  </tr>
                </thead>
                <tbody>
                  {/* Новая строка */}
                  {isAddingRow && (
                    <tr className="border-b bg-gray-50">
                      {schema.columns.map((column) => (
                        <td key={column.name} className="p-3">
                          <Input
                            value={newRow[column.name] || ''}
                            onChange={(e) => setNewRow({
                              ...newRow,
                              [column.name]: e.target.value
                            })}
                            placeholder={column.name}
                          />
                        </td>
                      ))}
                      <td className="p-3 text-right">
                        <div className="flex gap-2 justify-end">
                          <Button
                            size="sm"
                            variant="primary"
                            onClick={handleSaveNewRow}
                          >
                            <Save className="w-4 h-4" />
                          </Button>
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => setIsAddingRow(false)}
                          >
                            <X className="w-4 h-4" />
                          </Button>
                        </div>
                      </td>
                    </tr>
                  )}

                  {/* Существующие строки */}
                  {rows.map((row, index) => (
                    <tr key={index} className="border-b hover:bg-gray-50">
                      {schema.columns.map((column) => (
                        <td key={column.name} className="p-3">
                          {editingRow === index ? (
                            <Input
                              value={row[column.name] || ''}
                              onChange={(e) => {
                                const updatedRows = [...rows]
                                updatedRows[index] = {
                                  ...updatedRows[index],
                                  [column.name]: e.target.value
                                }
                                setRows(updatedRows)
                              }}
                            />
                          ) : (
                            <span>{row[column.name] || '-'}</span>
                          )}
                        </td>
                      ))}
                      <td className="p-3 text-right">
                        <div className="flex gap-2 justify-end">
                          {editingRow === index ? (
                            <>
                              <Button
                                size="sm"
                                variant="primary"
                                onClick={() => handleSaveEdit(index)}
                              >
                                <Save className="w-4 h-4" />
                              </Button>
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => setEditingRow(null)}
                              >
                                <X className="w-4 h-4" />
                              </Button>
                            </>
                          ) : (
                            <>
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => handleEditRow(index)}
                              >
                                <Edit className="w-4 h-4" />
                              </Button>
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => handleDeleteRow(index)}
                              >
                                <Trash2 className="w-4 h-4" />
                              </Button>
                            </>
                          )}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
