import { getCsrfToken } from "next-auth/react"

export default async function LoginPage() {
  const csrfToken = await getCsrfToken()
  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-yellow-100 to-yellow-300 medieval-bg">
      <div className="bg-white/90 rounded-xl shadow-lg p-8 max-w-md w-full border-4 border-yellow-700 medieval-border">
        <h1 className="text-3xl font-bold mb-6 text-yellow-900 medieval">Вход в летопись мастера</h1>
        <form method="post" action="/api/auth/callback/credentials" className="space-y-6">
          <input name="csrfToken" type="hidden" defaultValue={csrfToken} />
          <div>
            <label className="block text-lg font-semibold mb-2 medieval" htmlFor="email">Почта</label>
            <input className="w-full px-4 py-2 border-2 border-yellow-700 rounded medieval-input" type="email" id="email" name="email" required autoFocus />
          </div>
          <div>
            <label className="block text-lg font-semibold mb-2 medieval" htmlFor="password">Пароль</label>
            <input className="w-full px-4 py-2 border-2 border-yellow-700 rounded medieval-input" type="password" id="password" name="password" required />
          </div>
          <button type="submit" className="w-full py-2 px-4 bg-yellow-800 text-white font-bold rounded medieval-btn hover:bg-yellow-900 transition">Войти</button>
        </form>
      </div>
    </div>
  )
}
