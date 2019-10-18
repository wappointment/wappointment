<template>
  <div class="container-fluid">
    <div class="m-4">
      <div v-if="!messageSent">
        <h1>What's your question?</h1>
        <p><strong>Ask anything</strong>, whether you've <strong>encountered a bug</strong> or you're <strong>missing a feature</strong>, or you just want to <strong>say Hi</strong>.</p>
        <p>We'll try to answer you as quickly as possible.</p>
        <p>We speak English, mais aussi Français y tambien Español.</p>
      </div>
      <div v-if="serverObj">
        <Contact :autofill="autofillMessage" @sent="sent"/>
      </div>
    </div>
  </div>
</template>

<script>
import Contact from '../Wappointment/Contact'
import abstractview from './Abstract'
export default {
  extends: abstractview,
    components: {Contact},
    data: () => ({
        messageSent:false,
        viewName: 'serverinfo',
        parentLoad: false,
        serverObj: false
    }),
    created(){
      this.initValueRequest().then(this.loaded)
    },
    computed: {
      autofillMessage(){
            return {
                server: this.serverObj
            }
            
        },
    },
    methods:{
      loaded(response){
          this.serverObj  = response.data.server
      },
      sent(){
        this.messageSent = true
      }
    }
}
</script>



