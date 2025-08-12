# DM Copilot

AI-powered DnD session assistant for dungeon masters

## 🎯 Возможности

- **Knowledge Ingestion**: Загрузка и анализ модулей (PDF/MD/TXT)
- **Tables Engine**: Редактируемые справочники (напитки, NPC, зелья и др.)
- **Session Runner**: Два режима ведения сессии (ручной и живой с микрофоном)
- **Prompt Orchestrator**: Умная сборка контекста для AI-подсказок

## 🏗️ Архитектура

- **Frontend**: Next.js 14 (App Router) + React + Tailwind CSS
- **Backend**: Next.js API Routes
- **Database**: PostgreSQL + pgvector (Neon/Supabase)
- **AI**: DeepSeek API
- **STT**: Vosk (для живого режима)
- **Vectorization**: all-MiniLM-L6-v2

## 🚀 Быстрый старт

### Требования
- Node.js 18+
- pnpm 8+
- Docker (для локальной PostgreSQL)

### Установка

1. Клонируйте репозиторий
```bash
git clone <repository-url>
cd dm-copilot
```

2. Установите зависимости
```bash
pnpm install
```

3. Настройте переменные окружения
```bash
cp apps/web/.env.example apps/web/.env
# Отредактируйте .env файл
```

4. Запустите базу данных
```bash
docker-compose up -d postgres
```

5. Примените миграции
```bash
pnpm db:push
pnpm db:seed
```

6. Запустите приложение
```bash
pnpm dev
```

## 📁 Структура проекта

```
dm-copilot/
├── apps/
│   └── web/                 # Next.js приложение
├── packages/
│   ├── database/           # Prisma схемы и миграции
│   └── shared/             # Общие типы и утилиты
├── docker-compose.yml      # Локальная разработка
└── README.md
```

## 🔧 Конфигурация

### Переменные окружения

Создайте файл `apps/web/.env`:

```env
# Database
DATABASE_URL="postgresql://user:password@localhost:5432/dm_copilot"

# AI
DEEPSEEK_API_KEY="your-deepseek-api-key"
DEEPSEEK_MODEL="deepseek-chat"

# Vectorization
VECTOR_MODEL="all-MiniLM-L6-v2"

# STT (опционально)
VOSK_MODEL_PATH="./models/vosk-model-small-ru"
```

## 🎮 Использование

1. **Загрузка модуля**: Загрузите PDF/MD/TXT файл модуля
2. **Настройка таблиц**: Отредактируйте справочники NPC, зелий и др.
3. **Запуск сессии**: Выберите режим (ручной/живой) и начните игру
4. **Получение подсказок**: Система будет предлагать релевантные подсказки

## 🧪 Разработка

### Команды

```bash
# Разработка
pnpm dev

# Сборка
pnpm build

# Линтинг
pnpm lint

# База данных
pnpm db:generate  # Генерация Prisma клиента
pnpm db:push      # Применение схемы
pnpm db:migrate   # Создание миграции
pnpm db:seed      # Заполнение начальными данными
```

### Тестирование

```bash
pnpm test
```

## 📋 Roadmap

### MVP (v0.1.0)
- [x] Базовая архитектура
- [ ] Knowledge Ingestion
- [ ] Tables Engine
- [ ] Session Runner (ручной режим)
- [ ] Prompt Orchestrator

### v0.2.0
- [ ] Живой режим с микрофоном
- [ ] Аутентификация
- [ ] Экспорт/импорт кампаний

### v0.3.0
- [ ] Расширенные таблицы
- [ ] Аналитика сессий
- [ ] Мобильная версия

## 🤝 Вклад в проект

1. Fork репозитория
2. Создайте feature branch
3. Внесите изменения
4. Создайте Pull Request

## 📄 Лицензия

MIT License
