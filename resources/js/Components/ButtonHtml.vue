<template>
    <button @click="emitClick"><slot></slot></button>
</template>

<script>
export default {
  props: {
      text:''
  },
  methods: {
      emitClick(){
          this.$emit('next')
      },
  },

}
</script>
