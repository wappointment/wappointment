<template>
    <div class="ml-2 d-flex align-items-center">
        <button class="btn btn-secondary mr-2" @click="sendPreview">{{ get_i18n('send_preview', 'common') }}</button>
        <div>
            <div v-if="showRecipient">
                <InputPh v-model="dataRecipient" :ph="'Your email e.g.:'+dataRecipient"/>
            </div>
            <a href="javascript:;" v-else title="Edit" class="text-muted" 
            @mouseover="showRecipient=true" 
            @click="showRecipient=!showRecipient">to {{ dataRecipient }}</a>
        </div>
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