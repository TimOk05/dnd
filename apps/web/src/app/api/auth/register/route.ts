import { NextRequest, NextResponse } from 'next/server'
import { PrismaClient } from '@prisma/client'
import { hash } from 'bcryptjs'

const prisma = new PrismaClient()

export async function POST(request: NextRequest) {
  try {
    const { email, password } = await request.json()
    if (!email || !password) {
      return NextResponse.json({ error: 'Email и пароль обязательны' }, { status: 400 })
    }
    const existing = await prisma.user.findUnique({ where: { email } })
    if (existing) {
      return NextResponse.json({ error: 'Пользователь уже существует' }, { status: 409 })
    }
    const hashed = await hash(password, 10)
    await prisma.user.create({ data: { email, password: hashed } })
    return NextResponse.json({ success: true })
  } catch (e) {
    return NextResponse.json({ error: 'Ошибка регистрации' }, { status: 500 })
  }
}
