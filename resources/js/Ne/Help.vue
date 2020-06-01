<template>
  <div class="container-fluid">
    <div class="m-4">
      <div v-if="!messageSent">
        <div class="d-flex align-items-baseline">
          <h1>What's your question?</h1> <button v-if="serverObj" class="btn btn-link btn-sm" @click="show_changes=true">See all changes</button>
        </div>
        <p><strong>Ask anything</strong>, whether you've <strong>encountered a bug</strong> or you're <strong>missing a feature</strong>, or you just want to <strong>say Hi</strong>.</p>
        <p>We'll try to answer as quickly as possible.</p>
        <p>We speak English; tambien hablamos Español mais aussi Français.</p>
      </div>
      <div v-if="serverObj">
        <Contact :autofill="autofillMessage" @sent="sent"/>
      </div>
    </div>
    <VersionsInfos v-if="show_changes" :manual_show="true" @closed="closedChanges" />
  </div>
</template>

<script>
import Contact from '../Wappointment/Contact'
import abstractview from '../Views/Abstract'
import VersionsInfos from './VersionsInfos'
export default {
  extends: abstractview,
    components: {Contact, VersionsInfos},
    data: () => ({
        messageSent:false,
        viewName: 'serverinfo',
        parentLoad: false,
        serverObj: false,
        show_changes:false
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
      closedChanges(){
        this.show_changes = false
      },
      loaded(response){
          this.serverObj  = response.data.server
      },
      sent(){
        this.messageSent = true
      }
    }
}
</script>



