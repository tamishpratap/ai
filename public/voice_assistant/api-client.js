export class AssistantApi {
  constructor(config) {
    this.config = config;
  }

  getApiKey() {
    return (localStorage.getItem('user_gemini_api_key') || '').trim();
  }

  async request(url, options = {}) {
    const apiKey = this.getApiKey();
    const response = await fetch(url, {
      credentials: 'same-origin',
      ...options,
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': this.config.csrfToken,
        ...(apiKey ? { 'X-Gemini-Api-Key': apiKey } : {}),
        ...(options.body instanceof FormData ? {} : { 'Content-Type': 'application/json' }),
        ...(options.headers || {}),
      },
    });
    const payload = await response.json().catch(() => ({ success: false, message: 'Invalid server response.' }));
    if (!response.ok || !payload.success) {
      const error = new Error(payload.message || 'Request failed.');
      error.status = response.status;
      error.errors = payload.errors || {};
      throw error;
    }
    return payload.data || payload;
  }

  createLiveSession(conversationId = null) {
    return this.request(this.config.endpoints.session, {
      method: 'POST',
      body: JSON.stringify({
        conversation_id: conversationId,
        api_key: this.getApiKey() || null,
      }),
    });
  }

  saveLiveTurn(turn) {
    return this.request(this.config.endpoints.liveTurn, { method: 'POST', body: JSON.stringify(turn) });
  }

  sendText(message, conversationId, quickAction = null) {
    return this.request(this.config.endpoints.message, {
      method: 'POST',
      body: JSON.stringify({
        conversation_id: conversationId,
        message: message || null,
        quick_action: quickAction,
        speak: true,
        api_key: this.getApiKey() || null,
      }),
    });
  }

  summarizePdf(file, conversationId) {
    const body = new FormData();
    body.append('document', file);
    if (conversationId) body.append('conversation_id', conversationId);
    if (this.getApiKey()) body.append('api_key', this.getApiKey());
    return this.request(this.config.endpoints.pdf, { method: 'POST', body });
  }

  verifyKey(apiKey) {
    const endpoint = this.config.endpoints.verifyKey || '/api/assistant/verify-key';
    return this.request(endpoint, {
      method: 'POST',
      body: JSON.stringify({ api_key: apiKey }),
      headers: { 'X-Gemini-Api-Key': apiKey },
    });
  }

  generateImage() {
    return this.request(this.config.endpoints.image, { method: 'POST', body: '{}' });
  }

  stats() {
    return this.request(this.config.endpoints.stats);
  }
}
