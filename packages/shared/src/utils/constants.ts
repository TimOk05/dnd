// Константы для DM Copilot

export const APP_NAME = 'DM Copilot'
export const APP_VERSION = '0.1.0'

// Этапы сессии
export const SESSION_STAGES = {
  HOOK: 'Завязка',
  INTRO: 'Введение',
  TRAVEL: 'Путешествие',
  DUNGEON: 'Подземелье',
  ENCOUNTER: 'Столкновение',
  ROLEPLAY: 'Ролевая игра',
  CLIMAX: 'Кульминация',
  AFTERMATH: 'Развязка'
} as const

// Типы подсказок
export const SUGGESTION_TYPES = {
  PLOT_TWIST: 'plot_twist',
  NPC_DIALOGUE: 'npc_dialogue',
  SCENE_DESCRIPTION: 'scene_description',
  LOOT: 'loot',
  SKILL_CHECK: 'skill_check'
} as const

// Категории промптов
export const PROMPT_CATEGORIES = {
  SESSION: 'session',
  COMBAT: 'combat',
  ROLEPLAY: 'roleplay',
  EXPLORATION: 'exploration'
} as const

// Настройки по умолчанию
export const DEFAULT_CONFIG = {
  CHUNK_SIZE: 1000,
  CHUNK_OVERLAP: 200,
  MAX_TOKENS: 4000,
  TEMPERATURE: 0.7,
  TOP_P: 0.9,
  SIMILARITY_THRESHOLD: 0.7,
  MAX_SUGGESTIONS: 5
} as const

// Поддерживаемые форматы файлов
export const SUPPORTED_FORMATS = {
  PDF: '.pdf',
  MARKDOWN: '.md',
  TEXT: '.txt',
  JSON: '.json'
} as const

// Ограничения
export const LIMITS = {
  MAX_FILE_SIZE: 10 * 1024 * 1024, // 10MB
  MAX_CHUNKS_PER_DOCUMENT: 1000,
  MAX_EVENTS_PER_SESSION: 10000,
  MAX_TABLES: 100
} as const

// Сообщения об ошибках
export const ERROR_MESSAGES = {
  FILE_TOO_LARGE: 'Файл слишком большой',
  UNSUPPORTED_FORMAT: 'Неподдерживаемый формат файла',
  UPLOAD_FAILED: 'Ошибка загрузки файла',
  PROCESSING_FAILED: 'Ошибка обработки документа',
  AI_REQUEST_FAILED: 'Ошибка запроса к AI',
  DATABASE_ERROR: 'Ошибка базы данных'
} as const

// Успешные сообщения
export const SUCCESS_MESSAGES = {
  FILE_UPLOADED: 'Файл успешно загружен',
  DOCUMENT_PROCESSED: 'Документ обработан',
  SESSION_CREATED: 'Сессия создана',
  SUGGESTION_GENERATED: 'Подсказка сгенерирована'
} as const
