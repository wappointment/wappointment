<script>
export default {

    data: () => ({
        selection: false,
    }),

    computed: {
        phoneSelected(){
            return this.selection == 'phone'
        },
        physicalSelected(){
            return this.selection == 'physical'
        },
        skypeSelected(){
            return this.selection == 'skype'
        },
        zoomSelected(){
            return this.selection == 'zoom'
        },
    },
}
</script>