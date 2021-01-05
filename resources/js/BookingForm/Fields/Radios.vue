
<script>
import Checkboxes from './Checkboxes.vue'
export default {
    extends: Checkboxes,
    data: () => ({
        type:'radio',
        updateValue: '',
    }),
    created(){
        this.updateValue= false
    },
}   
</script>