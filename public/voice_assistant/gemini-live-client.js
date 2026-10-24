import { bytesToBase64 } from './audio-capture.js';

export class GeminiLiveClient {
  constructor(callbacks = {}) {
    this.callbacks = callbacks;
    this.socket = null;
  }

  connect(live, history = []) {
    return new Promise((resolve, reject) => {
      const url = `${live.websocket_url}?access_token=${encodeURIComponent(live.token)}`;
      this.socket = new WebSocket(url);
      const timeout = setTimeout(() => reject(new Error('Voice connection timed out.')), 12000);
      this.socket.onopen = () => this.socket.send(JSON.stringify({ setup: live.setup }));
      this.socket.onerror = () => this.callbacks.onError?.(new Error('Voice connection failed.'));
      this.socket.onclose = (event) => this.callbacks.onClose?.(event);
      this.socket.onmessage = async ({ data }) => {
        const message = JSON.parse(typeof data === 'string' ? data : await data.text());
        if (message.setupComplete) {
          clearTimeout(timeout);
          if (history.length) {
            this.socket.send(JSON.stringify({ clientContent: { turns: history, turnComplete: false } }));
          }
          this.callbacks.onOpen?.();
          resolve();
        }
        this.callbacks.onMessage?.(message);
      };
    });
  }

  sendAudio(bytes) {
    this.send({ realtimeInput: { audio: { data: bytesToBase64(bytes), mimeType: 'audio/pcm;rate=16000' } } });
  }

  endAudio() { this.send({ realtimeInput: { audioStreamEnd: true } }); }

  sendText(text) {
    this.send({ clientContent: { turns: [{ role: 'user', parts: [{ text }] }], turnComplete: true } });
  }

  send(payload) {
    if (this.socket?.readyState !== WebSocket.OPEN) throw new Error('Voice connection is not open.');
    this.socket.send(JSON.stringify(payload));
  }

  close() {
    if (this.socket?.readyState <= WebSocket.OPEN) this.socket.close(1000, 'Client closed');
    this.socket = null;
  }
}
