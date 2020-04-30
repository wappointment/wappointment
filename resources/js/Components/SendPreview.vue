<template>
    <div class="ml-2 d-flex align-items-center" v-if="viewData.mail_status">
        <button class="btn btn-secondary mr-2" @click="sendPreview">Send Preview</button>
        <div>
            <div v-if="showRecipient">
                <InputPh v-model="dataRecipient" :ph="'Your email e.g.:'+dataRecipient"/>
            </div>
            <a href="javascript:;" v-else title="Edit" class="text-muted" 
            @mouseover="showRecipient=true" 
            @click="showRecipient=!showRecipient">to {{ dataRecipient }}</a>
        </div>
        </div>
        
        <div class="bg-danger p-2 text-white rounded small ml-2" v-else> 
        <span class="dashicons dashicons-email"></span>
        <span>No emails will be sent without configuring the sending method first</span>  
    </div>
</template>
<script>
export default {
    props:['viewData', 'recipient'],
    components:{
        InputPh: window.wappoGet('InputPh')
    },
     data() {
      return {
          showRecipient:false,
          dataRecipient: false
      }
     },
     created(){
         this.dataRecipient = this.recipient
     },
    methods:{
        sendPreview() {
            this.$emit('sendPreview', this.dataRecipient)
        },
    }
}
</script>