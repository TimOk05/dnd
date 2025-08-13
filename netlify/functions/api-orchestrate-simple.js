exports.handler = async(event, context) => {
    const headers = {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': 'Content-Type',
        'Access-Control-Allow-Methods': 'POST, OPTIONS',
        'Content-Type': 'application/json'
    };

    if (event.httpMethod === 'OPTIONS') {
        return { statusCode: 200, headers, body: '' };
    }

    try {
        const body = JSON.parse(event.body);
        const { message, isChat = false } = body;

        if (!message) {
            return {
                statusCode: 400,
                headers,
                body: JSON.stringify({ error: 'Отсутствует сообщение' })
            };
        }

        const DEEPSEEK_API_KEY = process.env.DEEPSEEK_API_KEY;

        if (!DEEPSEEK_API_KEY) {
            return {
                statusCode: 500,
                headers,
                body: JSON.stringify({ error: 'API ключ не настроен' })
            };
        }

        const systemPrompt = isChat ?
            "Ты опытный мастер D&D, который помогает другим мастерам. Отвечай на русском языке, давай практичные советы по ведению игры, созданию сюжетов, балансировке встреч и правилам D&D. Будь дружелюбным и полезным." :
            "Ты опытный мастер D&D, который создает интересные элементы для игры. Отвечай на русском языке, создавай креативные и сбалансированные элементы для D&D сессий.";

        const response = await fetch('https://api.deepseek.com/v1/chat/completions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${DEEPSEEK_API_KEY}`
            },
            body: JSON.stringify({
                model: 'deepseek-chat',
                messages: [
                    { role: 'system', content: systemPrompt },
                    { role: 'user', content: message }
                ],
                max_tokens: 1000,
                temperature: 0.7
            })
        });

        if (!response.ok) {
            throw new Error(`DeepSeek API error: ${response.status}`);
        }

        const data = await response.json();
        const suggestion = data.choices[0].message.content;

        return {
            statusCode: 200,
            headers,
            body: JSON.stringify({
                suggestion: suggestion,
                tokens: data.usage ? .total_tokens || 0
            })
        };

    } catch (error) {
        console.error('API Error:', error);
        return {
            statusCode: 500,
            headers,
            body: JSON.stringify({
                error: 'Ошибка сервера',
                details: error.message
            })
        };
    }
};