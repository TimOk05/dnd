import { PrismaClient } from '@prisma/client'

const prisma = new PrismaClient()

async function main() {
  console.log('🌱 Начинаю заполнение базы данных...')

  // Таблица напитков
  await prisma.table.upsert({
    where: { name: 'drinks' },
    update: {},
    create: {
      name: 'drinks',
      schema: {
        columns: [
          { name: 'name', type: 'string', required: true },
          { name: 'region', type: 'string', required: true },
          { name: 'effect', type: 'string', required: true },
          { name: 'quirk', type: 'string', required: true }
        ]
      },
      rows: [
        {
          name: 'Эльфский Мед',
          region: 'Лесные королевства',
          effect: '+1 к харизме на 1 час',
          quirk: 'Говоришь стихами следующие 10 минут'
        },
        {
          name: 'Гномский Эль',
          region: 'Горные кланы',
          effect: '+1 к силе на 30 минут',
          quirk: 'Растет борода (даже у женщин)'
        },
        {
          name: 'Орчий Квас',
          region: 'Степные племена',
          effect: '+2 к выносливости на 2 часа',
          quirk: 'Хочешь сражаться со всеми вокруг'
        },
        {
          name: 'Драконье Вино',
          region: 'Древние руины',
          effect: 'Иммунитет к огню на 1 час',
          quirk: 'Дышишь дымом при разговоре'
        },
        {
          name: 'Ведьмин Отвар',
          region: 'Болота',
          effect: 'Видишь в темноте на 4 часа',
          quirk: 'Кожа светится в темноте'
        }
      ]
    }
  })

  // Таблица событий в пути
  await prisma.table.upsert({
    where: { name: 'events_travel' },
    update: {},
    create: {
      name: 'events_travel',
      schema: {
        columns: [
          { name: 'hook', type: 'string', required: true },
          { name: 'obstacle', type: 'string', required: true },
          { name: 'twist', type: 'string', required: true }
        ]
      },
      rows: [
        {
          hook: 'На дороге лежит раненый путник',
          obstacle: 'Он может быть замаскированным бандитом',
          twist: 'На самом деле это принц в изгнании'
        },
        {
          hook: 'Встречают торговый караван',
          obstacle: 'Караван атакуют разбойники',
          twist: 'Разбойники - это городская стража под прикрытием'
        },
        {
          hook: 'Находят заброшенную часовню',
          obstacle: 'Внутри обитает призрак',
          twist: 'Призрак охраняет сокровище'
        },
        {
          hook: 'Попадают в ловушку',
          obstacle: 'Ловушка срабатывает и все падают в яму',
          twist: 'В яме живет дружелюбный тролль'
        },
        {
          hook: 'Встречают странника',
          obstacle: 'Странник просит помощи',
          twist: 'Он бог в человеческом обличье'
        }
      ]
    }
  })

  // Таблица NPC
  await prisma.table.upsert({
    where: { name: 'npcs' },
    update: {},
    create: {
      name: 'npcs',
      schema: {
        columns: [
          { name: 'name', type: 'string', required: true },
          { name: 'role', type: 'string', required: true },
          { name: 'trait', type: 'string', required: true },
          { name: 'voice', type: 'string', required: true },
          { name: 'secret', type: 'string', required: true }
        ]
      },
      rows: [
        {
          name: 'Старик Торин',
          role: 'Трактирщик',
          trait: 'Всегда помнит имена гостей',
          voice: 'Грубый, но добрый',
          secret: 'Бывший королевский шпион'
        },
        {
          name: 'Леди Элара',
          role: 'Торговка',
          trait: 'Любит торговаться',
          voice: 'Мелодичная и убедительная',
          secret: 'Дочь местного лорда'
        },
        {
          name: 'Капитан Грим',
          role: 'Стражник',
          trait: 'Строго следует правилам',
          voice: 'Командный, авторитетный',
          secret: 'Тайно помогает преступникам'
        },
        {
          name: 'Мудрец Аларик',
          role: 'Маг',
          trait: 'Говорит загадками',
          voice: 'Тихий, задумчивый',
          secret: 'Потерял магические способности'
        },
        {
          name: 'Рыцарь Серена',
          role: 'Паладин',
          trait: 'Всегда защищает слабых',
          voice: 'Благородный, честный',
          secret: 'Сомневается в своей вере'
        }
      ]
    }
  })

  // Таблица зелий
  await prisma.table.upsert({
    where: { name: 'potions' },
    update: {},
    create: {
      name: 'potions',
      schema: {
        columns: [
          { name: 'name', type: 'string', required: true },
          { name: 'rarity', type: 'string', required: true },
          { name: 'effect', type: 'string', required: true },
          { name: 'side_effect', type: 'string', required: true }
        ]
      },
      rows: [
        {
          name: 'Зелье Левитации',
          rarity: 'Обычное',
          effect: 'Летаешь 10 минут',
          side_effect: 'Не можешь приземлиться добровольно'
        },
        {
          name: 'Эликсир Невидимости',
          rarity: 'Редкое',
          effect: 'Становишься невидимым на 1 час',
          side_effect: 'Твоя тень остается видимой'
        },
        {
          name: 'Настойка Силы',
          rarity: 'Обычное',
          effect: '+3 к силе на 1 час',
          side_effect: 'Становишься глупее (-2 к интеллекту)'
        },
        {
          name: 'Философский Камень',
          rarity: 'Легендарное',
          effect: 'Превращаешь металл в золото',
          side_effect: 'Стареешь на 10 лет за каждое использование'
        },
        {
          name: 'Зелье Регенерации',
          rarity: 'Редкое',
          effect: 'Восстанавливаешь все HP',
          side_effect: 'Растет лишняя конечность'
        }
      ]
    }
  })

  // Таблица названий таверн
  await prisma.table.upsert({
    where: { name: 'tavern_names' },
    update: {},
    create: {
      name: 'tavern_names',
      schema: {
        columns: [
          { name: 'prefix', type: 'string', required: true },
          { name: 'suffix', type: 'string', required: true },
          { name: 'vibe', type: 'string', required: true }
        ]
      },
      rows: [
        {
          prefix: 'Пьяный',
          suffix: 'Дракон',
          vibe: 'Шумная, веселая'
        },
        {
          prefix: 'Тихая',
          suffix: 'Русалка',
          vibe: 'Уютная, спокойная'
        },
        {
          prefix: 'Кривой',
          suffix: 'Кот',
          vibe: 'Подозрительная, темная'
        },
        {
          prefix: 'Золотой',
          suffix: 'Лев',
          vibe: 'Дорогая, элитная'
        },
        {
          prefix: 'Старый',
          suffix: 'Дуб',
          vibe: 'Традиционная, надежная'
        }
      ]
    }
  })

  // Пример пользователя-мастера
  const bcrypt = require('bcryptjs')
  const hashedPassword = await bcrypt.hash('password123', 10)
  await prisma.user.upsert({
    where: { email: 'master@example.com' },
    update: {},
    create: {
      email: 'master@example.com',
      password: hashedPassword,
    }
  })

  console.log('✅ База данных заполнена!')
}

main()
  .catch((e) => {
    console.error('❌ Ошибка при заполнении базы данных:', e)
    process.exit(1)
  })
  .finally(async () => {
    await prisma.$disconnect()
  })
