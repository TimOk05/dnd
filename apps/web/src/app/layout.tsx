import type { Metadata } from 'next'
import { Inter } from 'next/font/google'
import './globals.css'

const inter = Inter({ subsets: ['latin'] })

export const metadata: Metadata = {
  title: 'DM Copilot - AI Assistant for DnD Masters',
  description: 'AI-powered DnD session assistant for dungeon masters',
  keywords: ['DnD', 'Dungeon Master', 'AI', 'RPG', 'Tabletop'],
  authors: [{ name: 'DM Copilot Team' }],
  viewport: 'width=device-width, initial-scale=1',
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="ru">
      <body className={inter.className}>
        <div className="min-h-screen bg-gradient-to-br from-dark-50 to-dark-100">
          {children}
        </div>
      </body>
    </html>
  )
}
