<template>
    <div :class="wrapperClass">
      <button @click="showModal=true" :class="buttonClass"><span class="dashicons dashicons-testimonial"></span> {{ buttonLabel }}</button> 
      <WapModal v-if="showModal" :show="showModal" @hide="hideModal">
          <h4 slot="title" class="modal-title">{{ buttonLabel }}</h4>
          <Contact :autofill="autofillPassed" :errors="messages"/>
      </WapModal>
    </div>
</template>

<script>

import Contact from './Contact'
export default {
  components:{Contact},
  data: () => ({
      showModal: false,
  }),
  props: {
      subject: {
          type: String,
      },
      messages: {
          type: Array,
      },
      buttonLabel:{
        type: String,
        default: 'Contact us'
      },
      buttonClass: {
        type: String,
        default: 'btn btn-secondary'
      },
      wrapperClass: {
        type: String,
        default: 'align-self-center ml-auto'
      }
  },
  methods: {
    hideModal(){
      this.showModal = false
    }
  },
  computed: {
    autofillPassed(){
       return { subject: this.subject}
    },
  },
}
</script>