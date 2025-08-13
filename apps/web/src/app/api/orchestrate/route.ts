import { NextRequest, NextResponse } from 'next/server'
import { createDeepSeekClient, PROMPT_TEMPLATES } from '@/lib/ai'
import { AIContext, PromptTemplate } from '@dm-copilot/shared'

export async function POST(request: NextRequest) {
  try {
    const body = await request.json()
    const { context, templateType, type, customTemplate, message, isChat = false } = body

    // Поддержка старого формата (type) и нового (templateType)
    const finalTemplateType = templateType || type

    if (!context && !message) {
      return NextResponse.json(
        { error: 'Отсутствуют обязательные параметры: context или message' },
        { status: 400 }
      )
    }

    // Создание клиента DeepSeek
    const deepSeekClient = createDeepSeekClient()

    if (isChat) {
      // Режим чата
      const chatResponse = await deepSeekClient.chat(message, context || '')
      
      return NextResponse.json({
        success: true,
        suggestion: chatResponse.content,
        tokens: chatResponse.metadata.tokens,
        context: { message, isChat: true }
      })
    } else {
      // Режим генерации подсказок
      if (!finalTemplateType) {
        return NextResponse.json(
          { error: 'Отсутствует тип шаблона: templateType или type' },
          { status: 400 }
        )
      }

      // Валидация контекста
      const aiContext: AIContext = {
        sessionStage: context.sessionStage || '',
        masterNotes: context.masterNotes || '',
        moduleSummary: context.moduleSummary || '',
        recentEvents: context.recentEvents || [],
        relevantChunks: context.relevantChunks || [],
        tableData: context.tableData || []
      }

      // Получение шаблона промпта
      let promptTemplate: PromptTemplate

      if (customTemplate) {
        promptTemplate = customTemplate
      } else if (PROMPT_TEMPLATES[finalTemplateType]) {
        promptTemplate = PROMPT_TEMPLATES[finalTemplateType]
      } else {
        return NextResponse.json(
          { error: `Неизвестный тип шаблона: ${finalTemplateType}` },
          { status: 400 }
        )
      }

      // Генерация подсказки
      const suggestion = await deepSeekClient.generateSuggestion(aiContext, promptTemplate)

      // Логирование запроса (для аналитики)
      console.log(`AI Request: ${finalTemplateType}`, {
        sessionStage: aiContext.sessionStage,
        tokens: suggestion.metadata.tokens,
        timestamp: suggestion.metadata.timestamp
      })

      return NextResponse.json({
        success: true,
        suggestion: suggestion.content,
        tokens: suggestion.metadata.tokens,
        context: aiContext
      })
    }

  } catch (error) {
    console.error('Ошибка оркестрации промпта:', error)
    
    if (error instanceof Error && error.message.includes('DEEPSEEK_API_KEY')) {
      return NextResponse.json(
        { error: 'API ключ DeepSeek не настроен' },
        { status: 500 }
      )
    }

    return NextResponse.json(
      { error: 'Ошибка генерации подсказки' },
      { status: 500 }
    )
  }
}

// GET для получения доступных шаблонов
export async function GET() {
  try {
    const templates = Object.keys(PROMPT_TEMPLATES).map(key => ({
      type: key,
      name: getTemplateName(key),
      description: getTemplateDescription(key)
    }))

    return NextResponse.json({
      success: true,
      templates
    })
  } catch (error) {
    console.error('Ошибка получения шаблонов:', error)
    return NextResponse.json(
      { error: 'Ошибка получения шаблонов' },
      { status: 500 }
    )
  }
}

function getTemplateName(type: string): string {
  const names: Record<string, string> = {
    plot_twist: 'Поворот сюжета',
    npc_dialogue: 'Диалог NPC',
    scene_description: 'Описание сцены',
    loot_suggestion: 'Предложение лута',
    skill_check: 'Проверка навыков'
  }
  return names[type] || type
}

function getTemplateDescription(type: string): string {
  const descriptions: Record<string, string> = {
    plot_twist: 'Неожиданные повороты сюжета для удивления игроков',
    npc_dialogue: 'Живые диалоги для неигровых персонажей',
    scene_description: 'Атмосферные описания окружения',
    loot_suggestion: 'Сбалансированные награды и предметы',
    skill_check: 'Логичные проверки навыков для ситуации'
  }
  return descriptions[type] || 'Описание недоступно'
}
