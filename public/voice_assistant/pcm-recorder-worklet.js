class PcmRecorderProcessor extends AudioWorkletProcessor {
  constructor() {
    super();
    this.targetRate = 16000;
    this.pending = [];
    this.chunkSize = 2048;
    this.phase = 0;
  }

  process(inputs) {
    const input = inputs[0]?.[0];
    if (!input) return true;
    let sum = 0;
    const ratio = sampleRate / this.targetRate;
    for (let i = 0; i < input.length; i += 1) sum += input[i] * input[i];
    this.port.postMessage({ type: 'level', value: Math.min(1, Math.sqrt(sum / input.length) * 4) });

    while (this.phase < input.length) {
      const index = Math.floor(this.phase);
      const next = Math.min(index + 1, input.length - 1);
      const fraction = this.phase - index;
      this.pending.push(input[index] + (input[next] - input[index]) * fraction);
      this.phase += ratio;
    }
    this.phase -= input.length;

    while (this.pending.length >= this.chunkSize) {
      const samples = this.pending.splice(0, this.chunkSize);
      const pcm = new Int16Array(samples.length);
      for (let i = 0; i < samples.length; i += 1) {
        const value = Math.max(-1, Math.min(1, samples[i]));
        pcm[i] = value < 0 ? value * 0x8000 : value * 0x7fff;
      }
      this.port.postMessage({ type: 'audio', buffer: pcm.buffer }, [pcm.buffer]);
    }
    return true;
  }
}

registerProcessor('pcm-recorder', PcmRecorderProcessor);
