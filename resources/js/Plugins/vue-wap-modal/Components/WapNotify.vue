<template>
    <transition name="slide-fade-right">
        <div class="notify" v-if="!hasExpired" :class="notification.type" @mouseover="pause" @mouseout="resume">
            <div class="countdown" :style="getStyle"></div>
            <div class="content">
              <span class="close" @click="dismiss(notification)"></span>
              {{ notification.title }}
              <div v-if="hasSlot" class="small"><slot></slot></div>
            </div>
        </div>
    </transition>
</template>

<script>
import Timer from '../../../Standalone/timer.js'
export default {
  props: {
    notification: {
        type: Object,
    },
    duration: {
        type: Number,
        default: 5
    }
  },
  data() {
    return {
      hasExpired: false,
      timer: null,
      running: true
    };
  },
  created(){
      this.timer = new Timer(this.setExpired.bind(false, this.notification.id), this.getDuration * 1000)
  },
  computed:{
      
      getDuration(){
        return this.notification.duration === undefined ? this.duration: this.notification.duration
      },
      getStyle(){
          return 'animation-duration: ' + this.getDuration + 's;'
          +'animation-play-state: '+ (this.running ? 'running':'paused') +';'
      }
  },
  methods: {
    hasSlot (name = 'default') {
          return !!this.$slots[ name ] || !!this.$scopedSlots[ name ];
      },
      dismiss() {
        this.removeInstance()
      },
      setExpired(id){
          if(id == this.notification.id){
            this.hasExpired = true
            setTimeout(this.removeInstance,150)
          }
          
      },
      removeInstance(){
        this.$emit('expired', this.notification.id)
      },
      pause(){
          this.timer.pause()
          this.running = false
      },
      resume(){
          this.timer.resume()
          this.running = true
      },
      
  }

}
</script>
<style>
.notify-wrapper .notify {
  position: relative;
  width: 320px;
  border: none;
  background: #fff;
  color: #353535;
  box-shadow: .1rem .2rem .3rem rgba(0,0,0,.1);
  margin-bottom: 0.8rem;
  opacity: 1;
  right: 6%;
  overflow: hidden;
}
.notify .content{
  padding: .8rem;
  font-size: .9rem;
}
.notify .content .close {
    width: 32px;
    height: 32px;
    cursor: pointer;
}
.notify .content .close::after, .notify .content .close::before {
    height: 10px;
    width: 2px;
    position: absolute;
    content: ' ';
    position: absolute;
    background-color: #bbb;
    right: 22px;
    top: 4px;
}
.notify .content .close::after {
    transform: translateX(15px) rotate(-45deg);
}
.notify .content .close::before {
    transform: translateX(15px) rotate(45deg);
}
.notify.success {
  border-left: 4px solid #46b450;
}
.notify.error {
  border-left: 4px solid #dc3232;
}

.notify.success .countdown::after{
    background: #46b450;
}
.notify.error .countdown::after{
    background: #dc3232;
}
.notify .countdown::after{
    content: " ";
    background: #ccc;
    position: absolute;
    width: 100%;
    right: 100%;
    top: 0;
    height: .1rem;
    animation-name: countdown;
    animation-timing-function: linear;
    animation-play-state: inherit;
    animation-fill-mode: both;
    animation-duration: inherit;
}
.slide-fade-right-enter-active{
  transition: all .3s ease;
}

.slide-fade-right-leave-active{
  transition: all .1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}

.slide-fade-right-enter {
  transform: translateX(120%);
  opacity: 0;
}
.slide-fade-right-leave-to {
  transform: translateY(200%);
  opacity: 0;
}

@keyframes countdown {
  0% {
    right: 100%;
  }

  100% {
    right: 0;
  }
}
@media (max-width: 470px) { 
    .notify-wrapper .notify {
        width: 100%;
        position: initial;
    }
}
</style>

