<script>
import BlockMenu from './BlockMenu'
export default {
    extends: BlockMenu,
    props: ['progress'],
    created() {
        for (let index = 0; index < this.menus.length; index++) {
            const element = this.menus[index];
            if(index < parseInt(this.progress) - 1){
                this.menus[index].class = "crossed";
            }
        }
        if(this.progress > this.menus.length) {
            this.completed = true
            this.active = false
        }
    }
}
</script>