const { createDeepSeekClient, PROMPT_TEMPLATES } = require('../../apps/web/src/lib/ai');

exports.handler = async(event, context) => {
    // Включаем CORS для всех доменов
    const headers = {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': 'Content-Type',
        'Access-Control-Allow-Methods': 'POST, OPTIONS',
        'Content-Type': 'application/json'
    };

    // Обработка preflight запросов
    if (event.httpMethod === 'OPTIONS') {
        return {
            statusCode: 200,
            headers,
            body: ''
        };
    }

    try {
        const body = JSON.parse(event.body);
        const { context: sessionContext, templateType, type, customTemplate, message, isChat = false } = body;

        // Поддержка старого формата (type) и нового (templateType)
        const finalTemplateType = templateType || type;

        if (!sessionContext && !message) {
            return {
                statusCode: 400,
                headers,
                body: JSON.stringify({ error: 'Отсутствуют обязательные параметры: context или message' })
            };
        }

        // Создание клиента DeepSeek
        const deepSeekClient = createDeepSeekClient();

        if (isChat) {
            // Чат режим
            const response = await deepSeekClient.chat(message, sessionContext);
            return {
                statusCode: 200,
                headers,
                body: JSON.stringify({
                    suggestion: response.suggestion,
                    tokens: response.tokens || 0
                })
            };
        } else {
            // Обычный режим генерации
            const promptTemplate = PROMPT_TEMPLATES[finalTemplateType] || PROMPT_TEMPLATES.plot_twist;
            const response = await deepSeekClient.generateSuggestion(sessionContext, promptTemplate);

            return {
                statusCode: 200,
                headers,
                body: JSON.stringify({
                    suggestion: response.suggestion,
                    tokens: response.tokens || 0
                })
            };
        }

    } catch (error) {
        console.error('API Error:', error);
        return {
            statusCode: 500,
            headers,
            body: JSON.stringify({
                error: 'Внутренняя ошибка сервера',
                details: error.message
            })
        };
    }
};