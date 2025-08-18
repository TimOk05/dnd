"use client"

import { useState, useEffect, useRef } from "react"

const DEEPSEEK_API_KEY = "sk-1e898ddba737411e948af435d767e893"
const DEEPSEEK_API_URL = "https://api.deepseek.com/v1/chat/completions"

interface Message {
  role: "user" | "assistant"
  content: string
}

const QUICK_COMMANDS = [
  {
    label: "🎲 Бросить d20",
    prompt: "Брось d20 и выведи результат как мастер DnD."
  },
  {
    label: "🗣️ Создать NPC",
    prompt: `Сгенерируй случайного NPC для DnD и выведи результат в формате JSON со следующими полями:
{
  "name": "Имя персонажа",
  "race": "Раса",
  "class": "Класс",
  "traits": "Черты характера (коротко)",
  "appearance": "Описание внешности",
  "summary": "Короткая характеристика (1-2 предложения, выделить отдельно)"
}
Пиши только JSON, без пояснений.`
  },
  {
    label: "🚗 Событие в дороге",
    prompt: "Придумай интересное событие, которое может произойти с приключенцами в дороге."
  }
]

const RACES = ["Человек", "Эльф", "Дварф", "Полуорк", "Гном", "Тифлинг", "Полурослик", "Драконорожденный"];
const CLASSES = ["Воин", "Маг", "Паладин", "Плут", "Жрец", "Следопыт", "Бард", "Варвар", "Колдун"];
const TRAITS = [
  "Хитрый", "Честный", "Скрытный", "Добрый", "Жестокий", "Весёлый", "Молчаливый", "Циничный", "Отважный"
];

export default function AssistantPage() {
  const [messages, setMessages] = useState<Message[]>([])
  const [input, setInput] = useState("")
  const [isLoading, setIsLoading] = useState(false)
  const chatRef = useRef<HTMLDivElement>(null)

  // Новое состояние для формы NPC
  const [npcRace, setNpcRace] = useState("")
  const [npcClass, setNpcClass] = useState("")
  const [npcTrait, setNpcTrait] = useState("")

  // Загрузка истории из sessionStorage
  useEffect(() => {
    const saved = sessionStorage.getItem("assistant_chat")
    if (saved) setMessages(JSON.parse(saved))
  }, [])

  // Сохранение истории
  useEffect(() => {
    sessionStorage.setItem("assistant_chat", JSON.stringify(messages))
    // Скролл вниз
    if (chatRef.current) {
      chatRef.current.scrollTop = chatRef.current.scrollHeight
    }
  }, [messages])

  const sendMessage = async (content: string) => {
    if (!content.trim()) return
    const newMessages = [...messages, { role: "user", content }]
    setMessages(newMessages)
    setIsLoading(true)
    setInput("")
    try {
      const res = await fetch(DEEPSEEK_API_URL, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${DEEPSEEK_API_KEY}`
        },
        body: JSON.stringify({
          model: "deepseek-chat",
          messages: newMessages.map(m => ({ role: m.role, content: m.content }))
        })
      })
      const data = await res.json()
      const aiMessage = data.choices?.[0]?.message?.content || "[Ошибка AI]"
      setMessages([...newMessages, { role: "assistant", content: aiMessage }])
    } catch (e) {
      setMessages([...newMessages, { role: "assistant", content: "[Ошибка соединения с AI]" }])
    } finally {
      setIsLoading(false)
    }
  }

  // Новый prompt для AI с учётом выбранных параметров
  const buildNpcPrompt = () => {
    let prompt = `Сгенерируй NPC для DnD.`
    if (npcRace) prompt += ` Раса: ${npcRace}.`
    if (npcClass) prompt += ` Класс: ${npcClass}.`
    if (npcTrait) prompt += ` Черта характера: ${npcTrait}.`
    prompt += ` Остальные параметры (внешность, особенности поведения, краткая характеристика и т.д.) придумай сам, чтобы NPC был интересным и цельным. Верни результат в формате JSON: { "name": "...", "race": "...", "class": "...", "traits": "...", "appearance": "...", "behavior": "...", "summary": "..." } Пиши только JSON, без пояснений.`
    return prompt
  }

  const sendNpcRequest = () => {
    const prompt = buildNpcPrompt()
    sendMessage(prompt)
  }

  return (
    <div className="flex flex-col items-center justify-center min-h-screen bg-gray-50 p-4">
      <div className="w-full max-w-xl bg-white rounded-lg shadow-lg p-6 flex flex-col" style={{ minHeight: 500 }}>
        <h1 className="text-2xl font-bold text-center mb-4">AI-Чат для DnD Мастера</h1>
        {/* Новая форма выбора NPC */}
        <div className="mb-4 p-4 bg-yellow-50 rounded-lg border">
          <div className="mb-2 font-semibold">Создать NPC с параметрами:</div>
          <div className="flex flex-col gap-2 md:flex-row md:gap-4">
            <select className="border rounded px-2 py-1" value={npcRace} onChange={e => setNpcRace(e.target.value)}>
              <option value="">Раса (любой)</option>
              {RACES.map(r => <option key={r} value={r}>{r}</option>)}
            </select>
            <select className="border rounded px-2 py-1" value={npcClass} onChange={e => setNpcClass(e.target.value)}>
              <option value="">Класс (любой)</option>
              {CLASSES.map(c => <option key={c} value={c}>{c}</option>)}
            </select>
            <select className="border rounded px-2 py-1" value={npcTrait} onChange={e => setNpcTrait(e.target.value)}>
              <option value="">Черта (любая)</option>
              {TRAITS.map(t => <option key={t} value={t}>{t}</option>)}
            </select>
            <button
              className="bg-green-600 text-white px-4 py-2 rounded disabled:opacity-50"
              onClick={sendNpcRequest}
              disabled={isLoading}
            >
              Сгенерировать NPC
            </button>
          </div>
        </div>
        <div className="flex gap-2 mb-4 justify-center">
          {QUICK_COMMANDS.map(cmd => (
            <button
              key={cmd.label}
              className="px-3 py-2 bg-blue-100 hover:bg-blue-200 rounded text-sm"
              onClick={() => sendMessage(cmd.prompt)}
              disabled={isLoading}
            >
              {cmd.label}
            </button>
          ))}
        </div>
        <div
          ref={chatRef}
          className="flex-1 overflow-y-auto border rounded p-3 mb-4 bg-gray-50"
          style={{ minHeight: 300, maxHeight: 350 }}
        >
          {messages.length === 0 && (
            <div className="text-gray-400 text-center mt-16">Начните диалог с AI или используйте быстрые команды</div>
          )}
          {messages.map((msg, i) => {
            // Улучшенный парсер для секций NPC
            let npc = null
            if (msg.role === "assistant") {
              try {
                // 1. Попытка найти JSON
                const match = msg.content.match(/\{[\s\S]*\}/)
                if (match) {
                  npc = JSON.parse(match[0])
                } else {
                  // 2. Парсинг по секциям
                  const getSection = (label) => {
                    const regex = new RegExp(label + "[\n\r\s:–-]*([\s\S]*?)(?=\n[A-ZА-ЯЁ][^\n]*:|\n[A-ZА-ЯЁ][^\n]*\n|$)", "i")
                    return msg.content.match(regex)?.[1]?.trim() || null
                  }
                  const name = msg.content.match(/^[^\n]+/i)?.[0]?.trim()
                  const description = getSection("Описание")
                  const appearance = getSection("Внешность")
                  const traits = getSection("Черты характера")
                  const behavior = getSection("Особенности поведения")
                  const summaryRaw = getSection("Короткая характеристика")
                  // Парсим короткую характеристику на подпункты
                  let summary = summaryRaw
                  let summaryList = null
                  if (summaryRaw && /[А-ЯA-Zа-яa-z]+:/u.test(summaryRaw)) {
                    summaryList = summaryRaw.split(/\n|\r/).map(l => l.trim()).filter(Boolean)
                  }
                  if (name || description || appearance || traits || behavior || summary) {
                    npc = {
                      name: name || "NPC",
                      description,
                      appearance,
                      traits,
                      behavior,
                      summary: summaryList ? null : summary,
                      summaryList
                    }
                  }
                }
              } catch (e) {}
            }
            if (npc) {
              // Черты характера — списком, если есть переносы
              const traitsList = npc.traits ? npc.traits.split(/\n|\r|•|-/).map(t => t.trim()).filter(Boolean) : []
              return (
                <div key={i} className="mb-3 flex justify-start">
                  <div className="rounded-lg px-4 py-2 max-w-[80%] bg-green-100 text-left w-full">
                    <div className="mb-2 text-lg font-bold">
                      {npc.name}
                    </div>
                    {npc.description && (
                      <div className="mb-2">
                        <span className="font-semibold">Описание:</span> {npc.description}
                      </div>
                    )}
                    {npc.appearance && (
                      <div className="mb-2">
                        <span className="font-semibold">Внешность:</span> {npc.appearance}
                      </div>
                    )}
                    {traitsList.length > 0 && (
                      <div className="mb-2">
                        <span className="font-semibold">Черты характера:</span>
                        <ul className="list-disc ml-5">
                          {traitsList.map((t, idx) => <li key={idx}>{t}</li>)}
                        </ul>
                      </div>
                    )}
                    {npc.behavior && (
                      <div className="mb-2">
                        <span className="font-semibold">Особенности поведения:</span>
                        <ul className="list-disc ml-5">
                          {npc.behavior.split(/\n|\r|•|-/).map((b, idx) => b.trim() && <li key={idx}>{b.trim()}</li>)}
                        </ul>
                      </div>
                    )}
                    {npc.summaryList && (
                      <div className="mt-3 p-2 rounded bg-yellow-200 font-semibold">
                        <span className="font-semibold">Короткая характеристика:</span>
                        <ul className="list-disc ml-5">
                          {npc.summaryList.map((s, idx) => <li key={idx}>{s}</li>)}
                        </ul>
                      </div>
                    )}
                    {npc.summary && !npc.summaryList && (
                      <div className="mt-3 p-2 rounded bg-yellow-200 font-semibold text-center">
                        {npc.summary}
                      </div>
                    )}
                  </div>
                </div>
              )
            }
            // Обычный вывод для остальных сообщений
            return (
              <div key={i} className={`mb-3 flex ${msg.role === "user" ? "justify-end" : "justify-start"}`}>
                <div className={`rounded-lg px-4 py-2 max-w-[80%] ${msg.role === "user" ? "bg-blue-100 text-right" : "bg-green-100 text-left"}`}>
                  {msg.content}
                </div>
              </div>
            )
          })}
        </div>
        <form
          className="flex gap-2"
          onSubmit={e => {
            e.preventDefault()
            sendMessage(input)
          }}
        >
          <input
            className="flex-1 border rounded px-3 py-2"
            type="text"
            placeholder="Введите сообщение..."
            value={input}
            onChange={e => setInput(e.target.value)}
            disabled={isLoading}
            autoFocus
          />
          <button
            type="submit"
            className="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50"
            disabled={isLoading || !input.trim()}
          >
            Отправить
          </button>
        </form>
      </div>
    </div>
  )
}
