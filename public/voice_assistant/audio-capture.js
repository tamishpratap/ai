export class AudioCapture {
  constructor({ workletUrl, onAudio, onLevel, onError }) {
    Object.assign(this, { workletUrl, onAudio, onLevel, onError });
    this.stream = null;
    this.context = null;
  }

  async start() {
    if (!navigator.mediaDevices?.getUserMedia || !window.AudioWorkletNode) {
      throw new Error('This browser does not support real-time microphone streaming.');
    }
    this.stream = await navigator.mediaDevices.getUserMedia({
      audio: { channelCount: 1, echoCancellation: true, noiseSuppression: true, autoGainControl: true },
    });
    this.context = new AudioContext({ latencyHint: 'interactive' });
    await this.context.audioWorklet.addModule(this.workletUrl);
    this.source = this.context.createMediaStreamSource(this.stream);
    this.node = new AudioWorkletNode(this.context, 'pcm-recorder');
    this.silentGain = this.context.createGain();
    this.silentGain.gain.value = 0;
    this.node.port.onmessage = ({ data }) => {
      if (data.type === 'level') this.onLevel?.(data.value);
      if (data.type === 'audio') this.onAudio?.(new Uint8Array(data.buffer));
    };
    this.source.connect(this.node).connect(this.silentGain).connect(this.context.destination);
    this.stream.getAudioTracks()[0]?.addEventListener('ended', () => this.onError?.(new Error('Microphone disconnected.')));
  }

  async stop() {
    this.node?.disconnect();
    this.source?.disconnect();
    this.stream?.getTracks().forEach((track) => track.stop());
    if (this.context && this.context.state !== 'closed') await this.context.close();
    this.stream = this.context = this.node = this.source = null;
    this.onLevel?.(0);
  }
}

export function bytesToBase64(bytes) {
  let binary = '';
  for (let i = 0; i < bytes.length; i += 0x8000) {
    binary += String.fromCharCode(...bytes.subarray(i, i + 0x8000));
  }
  return btoa(binary);
}
