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
        <div class="controls" v-if="controls" >
            <strong class="timeText" :key="tBlockDisplay[0]+tBlockDisplay[1]">{{ tBlockDisplay[0] }}h - {{ tBlockDisplay[1] }}h <span class="dashicons dashicons-trash"  @click.prevent.stop="deleteBlock()"></span></strong>
        </div>
    </vue-draggable-resizable>
</template>

<script>
import VueDraggableResizable from '../Plugins/vue-draggable-resizable/vue-draggable-resizable'
export default {
    props: ['y', 'h', 'daykey', 'tblockid', 'timeBlock'],
    data() {
        return {
            controls:true,
            tBlockDisplay: [],
            minHour:0,
            maxHour:0,
            heightUnit:0,
        }
    },
    components: {
        VueDraggableResizable
    },
    created(){
        this.heightUnit = this.$parent.heightUnit
        this.minHour = this.$parent.minHour
        this.maxHour = this.$parent.maxHour
        this.tBlockDisplay = this.timeBlock
    },
    mounted(){
        this.$emit('deactive');
    },
    methods: {
      toggleControls(visible){
          this.controls = true;
      },
      keepActive(){
          this.$emit('active');
      },
      onResizeStop(x,y,w,h) { 
           if(this.y != y || this.h != h){
               this.$emit('updatedBlock', this.tblockid, this.getHourStart(y), this.getHourEnd(y,h));
           }
          
          this.$emit('deactive');
      },
      onDragstop(x,y) {
          if(this.y != y){
              this.$emit('updatedBlock', this.tblockid, this.getHourStart(y), this.getHourEnd(y,this.h));
          }
          this.$emit('deactive');
      },
      onDragSnapped(top){
          this.tBlockDisplay[0] = this.getHourStart(top)
          this.tBlockDisplay[1] = this.getHourEnd(top,this.h)
          //this.$emit('activeBlock', this.tblockid, this.tBlockDisplay);
      },
      onResizeSnapped(y,h){
        this.tBlockDisplay[0] = this.getHourStart(y)
        this.tBlockDisplay[1] = this.getHourEnd(y,h)
        //this.$emit('activeBlock', this.tblockid, this.tBlockDisplay);
      },
      

      getHourStart(y){
          return (y / this.heightUnit) + this.minHour ;
      },
      getHourEnd(y,h){
          return ((h) / this.heightUnit) + this.getHourStart(y)  ;
      },
      getHeightColumn(){
          return (this.maxHour - this.minHour) * this.heightUnit ;
      },


      deleteBlock(tblockid){
          this.$emit('deletedBlock', this.tblockid);  
      },


  } 
}
</script>
<style>

.controls .dashicons.dashicons-trash{
    cursor:pointer;
    color: #626262;
    font-size: 1rem;
}

.dashicons.dashicons-trash:hover{
    cursor:pointer;
    color:#b75241;
}

.controls {
    position: relative;
    top: 20%;
    text-align: center;
    font-size: .8rem;
}
</style>
