<template>
    <vue-draggable-resizable 
    @toggleControls="toggleControls" 
    @snapped="onDragSnapped" 
    @resizesnapped="onResizeSnapped" 
    @dragging="keepActive" 
    @resizing="keepActive" 
    @resizestop="onResizeStop" 
    @dragstop="onDragstop"
    @delete="deleteBlock"
    @editBlock="editBlock"
    :editable="editableBlock"
    :grid="getGrid"
    :y="y" 
    :h="h"
    >
        <div class="controls">
            <div class="timeText"  >
                <strong class="timeText" :key="start+end">{{ convertMinutesToTime(start) }} - {{ convertMinutesToTime(end) }} </strong>
            </div>
        </div>
        
    </vue-draggable-resizable>
</template>

<script>
import VueDraggableResizable from './vue-draggable-resizable/vue-draggable-resizable'
export default {
    props: ['y', 'h', 'daykey', 'tblockid', 'timeBlock', 'minHour', 'heightUnit'],
    data() {
        return {
            maxHour:0,
            start:0,
            end:0,
            editableBlock: false
        }
    },
    components: {
        VueDraggableResizable
    },
    created(){
        this.maxHour = this.$parent.maxHour
        this.start = this.timeBlock[0]
        this.end = this.timeBlock[1]
    },
    mounted(){
        this.$emit('deactive')
    },
    computed:{
        getGrid(){
            return [100, this.heightUnit]
        }
    },
    methods: {
        convertMinutesToTime(min){
            return Math.floor(min/60)+'h'+(min%60)
        },
        toggleControls(visible){
            this.showControls = visible
        },
        keepActive(){
            this.$emit('active')
        },
        onResizeStop(y, h) {
            if(this.y != y || this.h != h){
                this.$emit('updatedBlock', this.tblockid, this.getHourStart(y), this.getHourEnd(y,h), this.timeBlock)
            }
            
            this.$emit('deactive')
        },
        onDragstop(x,y) {

            if(this.y != y){
                this.$emit('updatedBlock', this.tblockid, this.getHourStart(y), this.getHourEnd(y,this.h), this.timeBlock)
            }
            this.$emit('deactive')
        },
        onDragSnapped(top){

            this.start = this.getHourStart(top)
            this.end = this.getHourEnd(top,this.h)

        },
        onResizeSnapped(y,h){
            //console.log('onResizeSnapped','y',y,'h',h)
            this.start = this.getHourStart(y)
            this.end = this.getHourEnd(y,h)
        },


        getHourStart (y){
            return y + this.minHour *60
        },
        getHourEnd(y,h){
            return h + this.getHourStart(y) 
        },
        getHeightColumn(){
            return (this.maxHour - this.minHour) * this.heightUnit
        },

        editBlock(){
            this.$emit('editBlock', this.tblockid) 
        },
        deleteBlock(){
            this.$emit('deletedBlock', this.tblockid) 
        },
  } 
}
</script>
<style>

.controls .dashicons{
    cursor:pointer;
    color: #fff;
    font-size: 1.2rem;
}

.controls .dashicons:hover{
    cursor:pointer;
    color:#b75241;
}

.controls {
    position: relative;
    top: 10%;
    text-align: center;
    font-size: .8rem;
}

</style>
