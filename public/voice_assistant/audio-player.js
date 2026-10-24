function decodeBase64(value) {
  const binary = atob(value);
  const bytes = new Uint8Array(binary.length);
  for (let i = 0; i < binary.length; i += 1) bytes[i] = binary.charCodeAt(i);
  return bytes;
}

export class StreamingPcmPlayer {
  constructor({ sampleRate = 24000, onLevel, onIdle }) {
    Object.assign(this, { sampleRate, onLevel, onIdle });
    this.sources = new Set();
    this.nextStart = 0;
  }

  async enqueue(base64) {
    if (!this.context) {
      this.context = new AudioContext({ latencyHint: 'interactive' });
      this.analyser = this.context.createAnalyser();
      this.analyser.fftSize = 256;
      this.analyser.smoothingTimeConstant = 0.72;
      this.analyser.connect(this.context.destination);
      this.frequencyData = new Uint8Array(this.analyser.frequencyBinCount);
      this.timeData = new Uint8Array(this.analyser.fftSize);
    }
    if (this.context.state === 'suspended') await this.context.resume();
    const bytes = decodeBase64(base64);
    const view = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);
    const samples = new Float32Array(Math.floor(bytes.byteLength / 2));
    for (let i = 0; i < samples.length; i += 1) {
      samples[i] = view.getInt16(i * 2, true) / 32768;
    }
    const buffer = this.context.createBuffer(1, samples.length, this.sampleRate);
    buffer.copyToChannel(samples, 0);
    const source = this.context.createBufferSource();
    source.buffer = buffer;
    source.connect(this.analyser);
    const start = Math.max(this.context.currentTime + 0.025, this.nextStart);
    this.nextStart = start + buffer.duration;
    this.sources.add(source);
    source.onended = () => {
      this.sources.delete(source);
      if (!this.sources.size) { this.onLevel?.(0); this.onIdle?.(); }
    };
    source.start(start);
  }

  stop() {
    this.sources.forEach((source) => { try { source.stop(); } catch (_) {} });
    this.sources.clear();
    this.nextStart = 0;
    this.onLevel?.(0);
  }

  getVisualData(barCount = 72) {
    if (!this.analyser || !this.sources.size) {
      return { level: 0, bars: new Float32Array(barCount) };
    }

    this.analyser.getByteFrequencyData(this.frequencyData);
    this.analyser.getByteTimeDomainData(this.timeData);

    let sum = 0;
    for (const sample of this.timeData) {
      const normalized = (sample - 128) / 128;
      sum += normalized * normalized;
    }

    const bars = new Float32Array(barCount);
    const usefulBins = Math.max(1, Math.floor(this.frequencyData.length * 0.72));
    for (let i = 0; i < barCount; i += 1) {
      const mirroredPosition = i < barCount / 2 ? i / (barCount / 2) : (barCount - 1 - i) / (barCount / 2);
      const bin = Math.min(usefulBins - 1, Math.floor(mirroredPosition * usefulBins));
      bars[i] = this.frequencyData[bin] / 255;
    }

    return { level: Math.min(1, Math.sqrt(sum / this.timeData.length) * 3.2), bars };
  }

  async close() {
    this.stop();
    if (this.context && this.context.state !== 'closed') await this.context.close();
    this.context = this.analyser = this.frequencyData = this.timeData = null;
  }
}
