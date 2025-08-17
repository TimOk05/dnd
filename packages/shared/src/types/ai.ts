export interface AIPrompt {
  system: string
  user: string
  context?: AIContext
}

export interface AIContext {
  sessionStage: string
  recentEvents: string[]
  relevantChunks: string[]
  tableData: any[]
  notes: string
}

export interface AIResponse {
  content: string
  suggestions: AISuggestion[]
  metadata: AIMetadata
}

export interface AISuggestion {
  type: 'plot_twist' | 'npc_dialogue' | 'scene_description' | 'loot' | 'skill_check'
  title: string
  content: string
  confidence: number
}

export interface AIMetadata {
  tokens_used: number
  model: string
  response_time: number
  context_length: number
}

export interface PromptTemplate {
  id: string
  name: string
  description: string
  system_prompt: string
  user_template: string
  variables: string[]
  category: 'session' | 'combat' | 'roleplay' | 'exploration'
}

export interface DeepSeekConfig {
  apiKey: string
  model: string
  maxTokens: number
  temperature: number
  topP: number
}

export interface VectorSearchResult {
  id: string
  text: string
  similarity: number
  metadata: any
}
