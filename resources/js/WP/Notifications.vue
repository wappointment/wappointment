<template>
    <transition name="fade-in">
        <div v-if="messages.length > 0" class="static notify" :class="type" role="alert">
            <div class="countdown"></div>
            <div class="content">
                <div class="h3" v-if="title">{{ title }}</div>
                <a v-if="!showDetails" href="javascript:;" @click="showDetails=true">Show details</a>
                <ul class="list-message" v-else>
                  <li v-for="message in messages">{{ message }}</li>
                </ul>
                <div v-if="isError">
                  <div class="border-top h5 pt-2 mt-2">Contact us, we'll help you!</div>
                  <ContactButton :subject="title" buttonLabel="Open a ticket" :autofill="autofill" :messages="messages" />
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import ContactButton from '../Wappointment/ContactButton'
export default {
  components: {ContactButton},
    props: {
        type: {
            type: String,
            default: 'error'
        },
        messages: {
            type: Array,
        },
        title: {
            type: String,
        },
        autofill: {
            type: Object,
        },
    },
    computed: {
      isError(){
        return this.type == 'error'
      }
    },
    data() {
      return {
          showDetails: false,
      } 
  },
}
</script>
<style>
.static.notify {
  position: relative;
  border: none;
  background: #fff;
  color: #353535;
  box-shadow: .1rem .2rem .3rem rgba(0,0,0,.1);
  opacity: 1;
  overflow: hidden;
  margin: 0;
}
.notify .content{
  padding: .8rem;
  font-size: .9rem;
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
.fade-in-enter-active{
  transition: all .3s ease;
}

.fade-in-leave-active{
  transition: all .1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}

.fade-in-enter {
  transform: translateY(120%);
  opacity: 0;
}
.fade-in-leave-to {
  transform: translateY(200%);
  opacity: 0;
}
.list-message {
  list-style: inside disclosure-closed;
}
</style>