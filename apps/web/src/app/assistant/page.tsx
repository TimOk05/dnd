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
    prompt += ` Остальные параметры (внешность, особенности поведения, краткая характеристика и т.д.) придумай сам, чтобы NPC был интересным и цельным. Верни результат строго в формате JSON (без пояснений, без текста до и после, без форматирования, только чистый JSON-объект): { "name": "...", "race": "...", "class": "...", "traits": "...", "appearance": "...", "behavior": "...", "summary": "..." }`;
    return prompt;
  }

  // Универсальный парсер NPC
  function parseNpc(content: string) {
    // 1. Попытка распарсить JSON
    try {
      const match = content.match(/\{[\s\S]*\}/)
      if (match) {
        return { npc: JSON.parse(match[0]), warning: false };
      }
    } catch {}
    // 2. Парсинг по ключевым словам
    const getSection = (label: string) => {
      const regex = new RegExp(label + "[\n\r\s:–-]*([\s\S]*?)(?=\n[A-ZА-ЯЁ][^\n]*:|\n[A-ZА-ЯЁ][^\n]*\n|$)", "i");
      return content.match(regex)?.[1]?.trim() || null;
    };
    const name = content.match(/^[^\n]+/i)?.[0]?.trim();
    const description = getSection("Описание");
    const appearance = getSection("Внешность");
    const traits = getSection("Черты характера");
    const behavior = getSection("Особенности поведения");
    const summary = getSection("Короткая характеристика");
    if (name || description || appearance || traits || behavior || summary) {
      return {
        npc: {
          name: name || "NPC",
          description,
          appearance,
          traits,
          behavior,
          summary
        },
        warning: false
      };
    }
    // 3. Не удалось структурировать
    return { npc: { raw: content }, warning: true };
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
            if (msg.role === "assistant") {
              const { npc, warning } = parseNpc(msg.content);
              if (npc && !npc.raw) {
                return (
                  <div key={i} className="mb-6 flex justify-start">
                    <div className="w-full max-w-lg mx-auto bg-white/90 shadow-xl rounded-2xl p-6 border border-yellow-200">
                      {/* Имя и раса/класс */}
                      <div className="flex items-center gap-3 mb-2">
                        <span className="text-3xl">🎭</span>
                        <span className="text-2xl font-extrabold text-gray-800">{npc.name}</span>
                      </div>
                      {(npc.race || npc.class) && (
                        <div className="text-sm text-gray-500 mb-4 ml-10">
                          {npc.race && <span>{npc.race}</span>}
                          {npc.race && npc.class && <span> • </span>}
                          {npc.class && <span>{npc.class}</span>}
                        </div>
                      )}
                      {/* Описание */}
                      {npc.description && (
                        <div className="italic text-gray-700 mb-4 ml-2">{npc.description}</div>
                      )}
                      {/* Внешность */}
                      {npc.appearance && (
                        <div className="mb-4 flex items-start gap-2 ml-2">
                          <span className="text-xl">👁️</span>
                          <span className="text-gray-800">{npc.appearance}</span>
                        </div>
                      )}
                      {/* Черты характера */}
                      {npc.traits && (
                        <div className="mb-4 ml-2">
                          <div className="flex items-center gap-2 mb-1">
                            <span className="text-xl">🧠</span>
                            <span className="font-semibold text-gray-700">Черты характера:</span>
                          </div>
                          <ul className="list-disc ml-8 text-gray-800">
                            {npc.traits.split(/\n|\r|•|-/).map((t, idx) => t.trim() && <li key={idx}>{t.trim()}</li>)}
                          </ul>
                        </div>
                      )}
                      {/* Особенности поведения */}
                      {npc.behavior && (
                        <div className="mb-4 ml-2">
                          <div className="flex items-center gap-2 mb-1">
                            <span className="text-xl">⚡</span>
                            <span className="font-semibold text-gray-700">Особенности поведения:</span>
                          </div>
                          <ul className="list-disc ml-8 text-gray-800">
                            {npc.behavior.split(/\n|\r|•|-/).map((b, idx) => b.trim() && <li key={idx}>{b.trim()}</li>)}
                          </ul>
                        </div>
                      )}
                      {/* Краткая характеристика */}
                      {npc.summary && (
                        <div className="mt-4 p-3 rounded-xl bg-yellow-100 border border-yellow-300 font-bold text-center text-yellow-900 shadow-inner text-lg flex items-center justify-center gap-2">
                          <span>⚔️</span>
                          <span>{npc.summary}</span>
                        </div>
                      )}
                    </div>
                  </div>
                );
              }
              // Если не удалось структурировать
              return (
                <div key={i} className="mb-3 flex justify-start">
                  <div className="rounded-lg px-4 py-2 max-w-[80%] bg-red-100 text-left w-full">
                    <div className="mb-2 font-bold text-red-700">AI не вернул структурированный ответ</div>
                    <div className="mb-2 whitespace-pre-line">{npc.raw}</div>
                    <button
                      className="mt-2 bg-yellow-500 text-white px-3 py-1 rounded"
                      onClick={() => sendNpcRequest()}
                      disabled={isLoading}
                    >
                      Попробовать ещё раз
                    </button>
                  </div>
                </div>
              );
            }
            // Обычный вывод для остальных сообщений
            return (
              <div key={i} className={`mb-3 flex ${msg.role === "user" ? "justify-end" : "justify-start"}`}>
                <div className={`rounded-lg px-4 py-2 max-w-[80%] ${msg.role === "user" ? "bg-blue-100 text-right" : "bg-green-100 text-left"}`}>
                  {msg.content}
                </div>
              </div>
            );
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
