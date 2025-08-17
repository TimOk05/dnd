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
    prompt: "Сгенерируй случайного NPC для DnD с именем, внешностью и короткой историей."
  },
  {
    label: "🚗 Событие в дороге",
    prompt: "Придумай интересное событие, которое может произойти с приключенцами в дороге."
  }
]

export default function AssistantPage() {
  const [messages, setMessages] = useState<Message[]>([])
  const [input, setInput] = useState("")
  const [isLoading, setIsLoading] = useState(false)
  const chatRef = useRef<HTMLDivElement>(null)

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

  return (
    <div className="flex flex-col items-center justify-center min-h-screen bg-gray-50 p-4">
      <div className="w-full max-w-xl bg-white rounded-lg shadow-lg p-6 flex flex-col" style={{ minHeight: 500 }}>
        <h1 className="text-2xl font-bold text-center mb-4">AI-Чат для DnD Мастера</h1>
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
          {messages.map((msg, i) => (
            <div key={i} className={`mb-3 flex ${msg.role === "user" ? "justify-end" : "justify-start"}`}>
              <div className={`rounded-lg px-4 py-2 max-w-[80%] ${msg.role === "user" ? "bg-blue-100 text-right" : "bg-green-100 text-left"}`}>
                {msg.content}
              </div>
            </div>
          ))}
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
