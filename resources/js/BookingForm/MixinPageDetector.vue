<script>
export default {
    computed: {
        isReschedulePage(){
            return this.viewData.indexOf('reschedule-event') !== -1
        },
        isCancelPage(){
            return this.viewData.indexOf('cancel-event') !== -1 
        },
        isSaveEventPage(){
            return this.viewData == 'add-event-to-calendar'
        },
        isViewEventPage(){
            return this.viewData == 'view-event'
        },
    },
    methods:{
        getText(textKey){
            return this.isCancelPage? this.options.cancel[textKey]:this.options.reschedule[textKey]
        },
    }
}
</script>