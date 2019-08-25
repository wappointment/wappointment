<template>
    <div class="d-flex bg-danger p-2 text-white rounded align-items-center" role="alert">
        <span v-if="nb.icon" :class="nb.icon"></span> {{ nb.title }}
        <button v-if="nb.button" class="btn ml-2 btn-sm btn-secondary" @click="buttonClick">{{ nb.button }}</button>
    </div>
</template>

<script>
export default {
  props: ['config','title', 'button', 'icon'],
  data() {
      return {
        nb: {
            title: '',
            button: '',
            icon: ''
        }
      }
  },

  created(){
      if(this.config){
          this.nb = Object.assign ({}, this.config)
      }else{
          this.nb.title = this.title
          this.nb.button = this.button
          this.nb.icon = this.icon
      }
  },
  methods: {
      buttonClick(){
          this.$emit('click')
      }
  }
}
</script>