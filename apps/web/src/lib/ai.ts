import { AIPrompt, AIContext, AIResponse, AISuggestion, PromptTemplate } from '@dm-copilot/shared'

const DEEPSEEK_API_URL = 'https://api.deepseek.com/v1/chat/completions'

export interface DeepSeekConfig {
  apiKey: string
  model: string
  maxTokens: number
  temperature: number
}

export class DeepSeekClient {
  private config: DeepSeekConfig

  constructor(config: DeepSeekConfig) {
    this.config = config
  }

  async generateSuggestion(
    context: AIContext,
    promptTemplate: PromptTemplate
  ): Promise<AISuggestion> {
    try {
      const prompt = this.buildPrompt(context, promptTemplate)
      
      const response = await fetch(DEEPSEEK_API_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${this.config.apiKey}`
        },
        body: JSON.stringify({
          model: this.config.model,
          messages: [
            {
              role: 'system',
              content: promptTemplate.systemPrompt
            },
            {
              role: 'user',
              content: prompt.userPrompt
            }
          ],
          max_tokens: this.config.maxTokens,
          temperature: this.config.temperature,
          stream: false
        })
      })

      if (!response.ok) {
        throw new Error(`DeepSeek API error: ${response.status} ${response.statusText}`)
      }

      const data = await response.json()
      const content = data.choices[0]?.message?.content

      if (!content) {
        throw new Error('Empty response from DeepSeek API')
      }

      return {
        type: promptTemplate.type,
        content,
        metadata: {
          model: this.config.model,
          tokens: data.usage?.total_tokens || 0,
          timestamp: new Date().toISOString()
        }
      }
    } catch (error) {
      console.error('Error generating AI suggestion:', error)
      throw error
    }
  }

  async chat(message: string, context: string = ''): Promise<AISuggestion> {
    try {
      const systemPrompt = `Ты опытный мастер подземелий D&D, помощник для ведущего игры. Твоя задача - помогать мастеру с советами, идеями и решениями игровых ситуаций. Отвечай кратко, но информативно, на русском языке.

Контекст сессии: ${context || 'Не указан'}

Ты можешь помочь с:
- Советами по ведению игры
- Идеями для сюжета
- Балансировкой боевых встреч
- Созданием NPC
- Решением сложных игровых ситуаций
- Правилами D&D
- И многим другим, связанным с D&D`

      const response = await fetch(DEEPSEEK_API_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${this.config.apiKey}`
        },
        body: JSON.stringify({
          model: this.config.model,
          messages: [
            {
              role: 'system',
              content: systemPrompt
            },
            {
              role: 'user',
              content: message
            }
          ],
          max_tokens: this.config.maxTokens,
          temperature: this.config.temperature,
          stream: false
        })
      })

      if (!response.ok) {
        throw new Error(`DeepSeek API error: ${response.status} ${response.statusText}`)
      }

      const data = await response.json()
      const content = data.choices[0]?.message?.content

      if (!content) {
        throw new Error('Empty response from DeepSeek API')
      }

      return {
        type: 'chat',
        content,
        metadata: {
          model: this.config.model,
          tokens: data.usage?.total_tokens || 0,
          timestamp: new Date().toISOString()
        }
      }
    } catch (error) {
      console.error('Error in chat:', error)
      throw error
    }
  }

  private buildPrompt(context: AIContext, template: PromptTemplate): AIPrompt {
    let userPrompt = template.userPrompt

    // Заменяем плейсхолдеры на реальные данные
    userPrompt = userPrompt.replace('{{session_stage}}', context.sessionStage || 'Не указано')
    userPrompt = userPrompt.replace('{{master_notes}}', context.masterNotes || 'Нет заметок')
    userPrompt = userPrompt.replace('{{recent_events}}', context.recentEvents?.join('\n') || 'Нет событий')
    
    if (context.moduleSummary) {
      userPrompt = userPrompt.replace('{{module_summary}}', context.moduleSummary)
    }

    if (context.relevantChunks && context.relevantChunks.length > 0) {
      const chunksText = context.relevantChunks
        .map(chunk => `- ${chunk.text}`)
        .join('\n')
      userPrompt = userPrompt.replace('{{relevant_chunks}}', chunksText)
    }

    if (context.tableData && context.tableData.length > 0) {
      const tableText = context.tableData
        .map(item => `- ${item.name}: ${JSON.stringify(item.data)}`)
        .join('\n')
      userPrompt = userPrompt.replace('{{table_data}}', tableText)
    }

    return {
      systemPrompt: template.systemPrompt,
      userPrompt
    }
  }
}

// Предустановленные шаблоны промптов
export const PROMPT_TEMPLATES: Record<string, PromptTemplate> = {
  plot_twist: {
    type: 'plot_twist',
    systemPrompt: `Ты опытный мастер подземелий D&D. Твоя задача - предложить неожиданный, но логичный поворот сюжета, который удивит игроков и добавит драматизма в игру.`,
    userPrompt: `Текущий этап сессии: {{session_stage}}
Заметки мастера: {{master_notes}}
Контекст модуля: {{module_summary}}
Релевантные фрагменты: {{relevant_chunks}}

Предложи 2-3 варианта неожиданных поворотов сюжета, которые подходят к текущей ситуации.`
  },

  npc_dialogue: {
    type: 'npc_dialogue',
    systemPrompt: `Ты создаешь диалоги для NPC в D&D. Диалоги должны быть живыми, соответствовать характеру персонажа и ситуации.`,
    userPrompt: `Этап сессии: {{session_stage}}
Контекст: {{master_notes}}
Релевантные данные: {{relevant_chunks}}

Создай 3-5 вариантов фраз для NPC, которые подходят к текущей ситуации.`
  },

  scene_description: {
    type: 'scene_description',
    systemPrompt: `Ты описываешь сцены в D&D. Описания должны быть атмосферными, детальными и погружать игроков в мир.`,
    userPrompt: `Этап: {{session_stage}}
Контекст модуля: {{module_summary}}
Релевантные фрагменты: {{relevant_chunks}}

Создай яркое описание текущей сцены, которое поможет игрокам представить окружение.`
  },

  loot_suggestion: {
    type: 'loot_suggestion',
    systemPrompt: `Ты предлагаешь награды и лут в D&D. Награды должны быть сбалансированными и подходящими к ситуации.`,
    userPrompt: `Этап сессии: {{session_stage}}
Контекст: {{master_notes}}
Данные о предметах: {{table_data}}

Предложи подходящие награды для текущей ситуации.`
  },

  skill_check: {
    type: 'skill_check',
    systemPrompt: `Ты создаешь проверки навыков в D&D. Они должны быть логичными и добавлять напряжение в игру.`,
    userPrompt: `Этап: {{session_stage}}
Ситуация: {{master_notes}}
Контекст: {{relevant_chunks}}

Предложи 2-3 проверки навыков, которые подходят к текущей ситуации.`
  }
}

// Создание экземпляра клиента
export function createDeepSeekClient(): DeepSeekClient {
  const apiKey = process.env.DEEPSEEK_API_KEY
  if (!apiKey) {
    throw new Error('DEEPSEEK_API_KEY не установлен в переменных окружения')
  }

  return new DeepSeekClient({
    apiKey,
    model: process.env.DEEPSEEK_MODEL || 'deepseek-chat',
    maxTokens: parseInt(process.env.DEEPSEEK_MAX_TOKENS || '1000'),
    temperature: parseFloat(process.env.DEEPSEEK_TEMPERATURE || '0.7')
  })
}
