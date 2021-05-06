<script>
export default {
    data: () => ({
        showModal: false,
        modalTitle: '',
    }),
    methods:{
        openPopup(modalTitle){
            this.modalTitle = modalTitle
            this.showModal = true
        },
        hidePopup(modalTitle){
            this.modalTitle = 'popTitle'
            this.showModal = false
            if(this.hideElementsPopup!== undefined){
                this.hideElementsPopup()
            }
        },

    }
}
</script>

