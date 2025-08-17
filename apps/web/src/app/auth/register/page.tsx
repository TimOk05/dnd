"use client"
import { useState } from "react"

export default function RegisterPage() {
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [confirm, setConfirm] = useState("")
  const [error, setError] = useState("")
  const [success, setSuccess] = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError("")
    setSuccess(false)
    if (password !== confirm) {
      setError("Пароли не совпадают")
      return
    }
    const res = await fetch("/api/auth/register", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, password })
    })
    if (res.ok) setSuccess(true)
    else setError("Ошибка регистрации. Возможно, такой email уже существует.")
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-yellow-100 to-yellow-300 medieval-bg">
      <div className="bg-white/90 rounded-xl shadow-lg p-8 max-w-md w-full border-4 border-yellow-700 medieval-border">
        <h1 className="text-3xl font-bold mb-6 text-yellow-900 medieval">Регистрация мастера</h1>
        <form className="space-y-6" onSubmit={handleSubmit}>
          <div>
            <label className="block text-lg font-semibold mb-2 medieval" htmlFor="email">Почта</label>
            <input className="w-full px-4 py-2 border-2 border-yellow-700 rounded medieval-input" type="email" id="email" value={email} onChange={e => setEmail(e.target.value)} required />
          </div>
          <div>
            <label className="block text-lg font-semibold mb-2 medieval" htmlFor="password">Пароль</label>
            <input className="w-full px-4 py-2 border-2 border-yellow-700 rounded medieval-input" type="password" id="password" value={password} onChange={e => setPassword(e.target.value)} required />
          </div>
          <div>
            <label className="block text-lg font-semibold mb-2 medieval" htmlFor="confirm">Повторите пароль</label>
            <input className="w-full px-4 py-2 border-2 border-yellow-700 rounded medieval-input" type="password" id="confirm" value={confirm} onChange={e => setConfirm(e.target.value)} required />
          </div>
          {error && <div className="text-red-700 font-semibold medieval">{error}</div>}
          {success && <div className="text-green-700 font-semibold medieval">Регистрация успешна! Теперь вы можете войти.</div>}
          <button type="submit" className="w-full py-2 px-4 bg-yellow-800 text-white font-bold rounded medieval-btn hover:bg-yellow-900 transition">Зарегистрироваться</button>
        </form>
      </div>
    </div>
  )
}
