

export default class SoundsEnviroment {

  constructor(template={}) {
    this.sounds = template.sounds || {};
    this.audio = {};
    if(this.sounds.startFlip) {
      this.audio.startFlip = new Audio(this.sounds.startFlip);
    }
    if(this.sounds.endFlip) {
      this.audio.endFlip = new Audio(this.sounds.endFlip);
    }
  }

  setEnabled(enabled) {
    this.enabled = enabled;
  }

  togle() {
    this.enabled = !this.enabled;
  }

  dispose() {
    delete this.audio.startFlip;
    delete this.audio.endFlip;
  }

  startFlip() {
    if(this.enabled && this.audio.startFlip) {
      this.audio.startFlip.play();
    }
  }

  endFlip() {
    if(this.enabled && this.audio.startFlip) {
      this.audio.startFlip.pause();
      this.audio.startFlip.currentTime = 0;
    }
    if(this.enabled && this.audio.endFlip) {
      this.audio.endFlip.play();
    }
  }

  subscribeFlips(emitter) {
    emitter.addEventListener('startFlip', this.startFlip.bind(this));
    emitter.addEventListener('endFlip', this.endFlip.bind(this));
  }

}
