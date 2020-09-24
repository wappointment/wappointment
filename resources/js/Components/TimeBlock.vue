<template>
    <vue-draggable-resizable 
    @toggleControls="toggleControls" 
    @snapped="onDragSnapped" 
    @resizesnapped="onResizeSnapped" 
    @dragging="keepActive" 
    @resizing="keepActive" 
    @resizestop="onResizeStop" 
    @dragstop="onDragstop" 
    :y="y" 
    :h="h"
    >
        <div class="controls">
            <div class="timeText"  >
                <strong class="timeText" :key="start+end">{{ start }}h - {{ end }}h 
                </strong>
            </div>
            <strong class="timeText">
                <span class="dashicons dashicons-trash"  @click.prevent.stop="deleteBlock"></span>
            </strong>
            
        </div>
        
    </vue-draggable-resizable>
</template>

<script>
import VueDraggableResizable from '../Plugins/vue-draggable-resizable/vue-draggable-resizable'
export default {
    props: ['y', 'h', 'daykey', 'tblockid', 'timeBlock'],
    data() {
        return {
            minHour:0,
            maxHour:0,
            heightUnit:0,
            start:0,
            end:0
        }
    },
    components: {
        VueDraggableResizable
    },
    created(){
        this.heightUnit = this.$parent.heightUnit
        this.minHour = this.$parent.minHour
        this.maxHour = this.$parent.maxHour
        this.start = this.timeBlock[0]
        this.end = this.timeBlock[1]
    },
    mounted(){
        this.$emit('deactive')
    },
    methods: {
      toggleControls(visible){
          this.showControls = visible
      },
      keepActive(){
          this.$emit('active')
      },
      onResizeStop(x,y,w,h) { 
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
          this.start = this.getHourStart(y)
          this.end = this.getHourEnd(y,h)
      },
      

      getHourStart (y){
          //console.log('getHourStart y', y ,(y / this.heightUnit) + this.minHour )
          return (y / this.heightUnit) + this.minHour 
      },
      getHourEnd(y,h){
          //console.log('getHourEnd y h', y, h ,((h) / this.heightUnit) + this.getHourStart(y)  )
          return ((h) / this.heightUnit) + this.getHourStart(y) 
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
