'use client'

import { useState } from 'react'
import Link from 'next/link'
import { 
  BookOpen, 
  Table, 
  Play, 
  Upload, 
  Settings, 
  Sparkles,
  Users,
  Sword,
  Map,
  Crown
} from 'lucide-react'

export default function HomePage() {
  const [isLoading, setIsLoading] = useState(false)

  const features = [
    {
      icon: <Upload className="w-6 h-6" />,
      title: 'Knowledge Ingestion',
      description: 'Загружайте модули и автоматически извлекайте знания',
      href: '/ingest'
    },
    {
      icon: <Table className="w-6 h-6" />,
      title: 'Tables Engine',
      description: 'Редактируемые справочники NPC, зелий и событий',
      href: '/tables'
    },
    {
      icon: <Play className="w-6 h-6" />,
      title: 'Session Runner',
      description: 'Ведите сессии в ручном или живом режиме',
      href: '/session'
    },
    {
      icon: <Sparkles className="w-6 h-6" />,
      title: 'AI Assistant',
      description: 'Умные подсказки и рекомендации от DeepSeek',
      href: '/assistant'
    }
  ]

  const quickActions = [
    {
      icon: <Users className="w-5 h-5" />,
      title: 'Создать NPC',
      description: 'Быстро создать нового персонажа',
      action: () => window.location.href = '/tables/npcs'
    },
    {
      icon: <Sword className="w-5 h-5" />,
      title: 'Случайное событие',
      description: 'Генерировать случайное событие',
      action: () => window.location.href = '/tables/events_travel'
    },
    {
      icon: <Map className="w-5 h-5" />,
      title: 'Название таверны',
      description: 'Создать название для таверны',
      action: () => window.location.href = '/tables/tavern_names'
    },
    {
      icon: <Crown className="w-5 h-5" />,
      title: 'Новая сессия',
      description: 'Создать новую сессию D&D',
      action: () => window.location.href = '/session'
    }
  ]

  return (
    <div className="container mx-auto px-4 py-8">
      {/* Hero Section */}
      <div className="text-center mb-12">
        <div className="flex items-center justify-center mb-4">
          <BookOpen className="w-12 h-12 text-primary-600 mr-3" />
          <h1 className="text-4xl font-bold text-dark-900">
            DM Copilot
          </h1>
        </div>
        <p className="text-xl text-dark-600 max-w-2xl mx-auto">
          AI-ассистент для мастеров DnD. Загружайте модули, управляйте таблицами 
          и получайте умные подсказки во время сессий.
        </p>
      </div>

      {/* Features Grid */}
      <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        {features.map((feature, index) => (
          <Link
            key={index}
            href={feature.href}
            className="card p-6 hover:shadow-md transition-shadow group"
          >
            <div className="flex items-center mb-4">
              <div className="p-2 bg-primary-100 rounded-lg group-hover:bg-primary-200 transition-colors">
                {feature.icon}
              </div>
            </div>
            <h3 className="text-lg font-semibold text-dark-900 mb-2">
              {feature.title}
            </h3>
            <p className="text-dark-600 text-sm">
              {feature.description}
            </p>
          </Link>
        ))}
      </div>

      {/* Quick Actions */}
      <div className="card p-6 mb-8">
        <h2 className="text-2xl font-bold text-dark-900 mb-6">
          Быстрые действия
        </h2>
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
          {quickActions.map((action, index) => (
            <button
              key={index}
              onClick={action.action}
              disabled={isLoading}
              className="flex items-center p-4 border border-dark-200 rounded-lg hover:bg-dark-50 transition-colors text-left"
            >
              <div className="p-2 bg-secondary-100 rounded-lg mr-3">
                {action.icon}
              </div>
              <div>
                <h3 className="font-medium text-dark-900">
                  {action.title}
                </h3>
                <p className="text-sm text-dark-600">
                  {action.description}
                </p>
              </div>
            </button>
          ))}
        </div>
      </div>

      {/* Stats */}
      <div className="grid md:grid-cols-3 gap-6 mb-8">
        <div className="card p-6 text-center">
          <div className="text-3xl font-bold text-primary-600 mb-2">0</div>
          <div className="text-dark-600">Загруженных модулей</div>
        </div>
        <div className="card p-6 text-center">
          <div className="text-3xl font-bold text-secondary-600 mb-2">5</div>
          <div className="text-dark-600">Таблиц данных</div>
        </div>
        <div className="card p-6 text-center">
          <div className="text-3xl font-bold text-dark-600 mb-2">0</div>
          <div className="text-dark-600">Проведенных сессий</div>
        </div>
      </div>

      {/* Getting Started */}
      <div className="card p-6">
        <h2 className="text-2xl font-bold text-dark-900 mb-4">
          Начать работу
        </h2>
        <div className="space-y-4">
          <div className="flex items-start">
            <div className="flex-shrink-0 w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center mr-3 mt-1">
              <span className="text-primary-600 font-semibold text-sm">1</span>
            </div>
            <div>
              <h3 className="font-medium text-dark-900">Загрузите модуль</h3>
              <p className="text-dark-600 text-sm">
                Загрузите PDF, Markdown или текстовый файл с описанием модуля
              </p>
            </div>
          </div>
          <div className="flex items-start">
            <div className="flex-shrink-0 w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center mr-3 mt-1">
              <span className="text-primary-600 font-semibold text-sm">2</span>
            </div>
            <div>
              <h3 className="font-medium text-dark-900">Настройте таблицы</h3>
              <p className="text-dark-600 text-sm">
                Отредактируйте справочники NPC, зелий и других элементов
              </p>
            </div>
          </div>
          <div className="flex items-start">
            <div className="flex-shrink-0 w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center mr-3 mt-1">
              <span className="text-primary-600 font-semibold text-sm">3</span>
            </div>
            <div>
              <h3 className="font-medium text-dark-900">Запустите сессию</h3>
              <p className="text-dark-600 text-sm">
                Создайте новую сессию и получайте AI-подсказки в реальном времени
              </p>
            </div>
          </div>
        </div>
        <div className="mt-6">
          <Link
            href="/ingest"
            className="btn btn-primary btn-lg"
          >
            Начать с загрузки модуля
          </Link>
        </div>
      </div>
    </div>
  )
}
