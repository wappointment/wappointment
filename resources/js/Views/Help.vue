<template>
  <div class="container-fluid">
    <div class="m-4">
      <div v-if="!messageSent">
        <h1>What's your question?</h1>
        <p><strong>Ask anything</strong>, whether you've <strong>encountered a bug</strong> or you're <strong>missing a feature</strong>, or you just want to <strong>say Hi</strong>.</p>
        <p>We'll try to answer you as quickly as possible.</p>
        <p>We speak English, mais aussi Français y tambien Español.</p>
      </div>
      <div>
        <Contact @sent="sent"/>
      </div>
    </div>
  </div>
</template>

<script>
import Contact from '../Wappointment/Contact'
export default {
    components: {Contact},
    data: () => ({
        messageSent:false,
    }),
    methods:{
      sent(){
        this.messageSent = true
      }
    }
}
</script>



