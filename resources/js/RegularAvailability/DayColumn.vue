<template>
    <div :class="classColumn">
        <strong class="columnTitle">{{ daykey }}</strong>
        <div :id="daykey" :class=" active ? 'box-shadow active' : 'box-shadow'" :style="'height:'+getHeightColumn+'px'"
            draggable="true" 
            @mousedown.prevent.stop="dragging"
            @touchstart.prevent.stop="dragging"
            @mousemove="draggingMove"
            @touchmove="touchMove"
            @dragstart.prevent.self="startCreate"
            @mouseup.prevent.stop="stopCreate"
            @touchend.prevent.stop="toucheComplete"
            @touchcancel.prevent.stop="toucheComplete">
            
            <div class="drag-helper" :style="getWrapperGhostStyle"></div>
            <div class="ghost-wrapper" :style="getWrapperGhostStyle">
                <div v-if="isAtLeastOneSlot" :style="getGhostStyle" class="ghost">
                    <strong class="timeText">{{ convertMinutesToTime(ghost[0]) }} - {{ convertMinutesToTime(ghost[1]) }}</strong>
                </div>
            </div>
            <div class="events" v-if="!hiddenTimes">
                
                <timeBlock v-for="(timeBlock, tblockid) in openedTimes" :key="minHour+maxHour+daykey+tblockid+timeBlock[0]+timeBlock[1]" 
                :tblockid="tblockid" :timeBlock="timeBlock" 
                :daykey="daykey" :y="getY(timeBlock[0])" 
                :h="getHeight(timeBlock)"
                :minHour="minHour"
                :heightUnit="heightUnit"
                @deletedBlock="deletedBlock" 
                @updatedBlock="updatedBlock"
                @active="activeDay"
                @deactive="deactiveDay"
                @editBlock="editBlock"
                ></timeBlock>
            
            </div>
        </div>
    </div>
</template>

<script>
import timeBlock from './TimeBlock'

let timeBlockComps = window.wappointmentExtends.filter('RegavTimeBlockComponent', {'timeBlock': timeBlock} )
export default {
    props: ['daykey', 'openedTimes', 'minHour', 'maxHour', 'classColumn', 'heightUnit', 'precision'],
    data() {
        return {
            notchSize: 1,
            startDragAt: 0,
            currentDrag: 0,
            isDragging: false,
            ghost: [],
            lastLayerY: 0,
            active: false,
            activeBlock: [],
            hiddenTimes: false
        }
    },
    components: timeBlockComps, 
    computed: {
        isAtLeastOneSlot(){
            return this.isDragging && (this.ghost[1]-this.ghost[0]) != 0
        },
        getGhostStyle(){
          return this.ghost.length > 0 ? 'height:'+this.getHeight(this.ghost)+'px;top:'+this.getY(this.ghost[0])+'px;':''
        },
        getWrapperGhostStyle(){
           return this.ghost.length > 0 ? 'position:absolute;height: 100%;width: 100%;':''
        },
        getHeightColumn(){
          return this.totalIntervals * this.heightUnit 
        },

        totalIntervals(){
            return (this.maxHour - this.minHour) * this.intervalsPerHour
        },

        intervalsPerHour(){
            return (60/this.precision)
        },
    },
    mounted(){
        this.deactiveDay()
    },
    methods: {
    convertMinutesToTime(min){
        return ( Math.floor(min/60))+'h'+(min%60== 0 ? '':min%60)
    },
      activeDay(){
          this.active = true
      },
      deactiveDay(){
          this.active = false
      },
      startCreate(e){
          this.initGhost(e)
      },
      
      getYPositionInCol(e){
          let parentDiv = document.getElementById(this.daykey).getBoundingClientRect()
          if (e.type.indexOf('touch') !== -1) {
             return e.changedTouches[0].clientY - parentDiv.top
        } else {
             return e.layerY
        }
      },
      initGhost(e){
          this.startDragAt = this.getYPositionInCol(e)
          this.currentDrag = this.startDragAt + this.heightUnit
      },
        toucheComplete(e){
            this.stopCreate(e)
        },
      stopCreate(e){
          if(this.isDragging) {
              this.createBlock(e)
          }
          
          this.ghost = []
          this.deactiveDay()
      },

      dragging(e){
          this.initGhost(e)
          this.isDragging = true
          this.activeDay()
      },
        touchMove(e){
            return this.draggingMove(e)
        },
      draggingMove(e){

         if(!this.isDragging || !(e.target.className=='events' || e.target.className=='drag-helper' || e.target.className=='ghost')) {
             return;
         }
         
          if(e.target.className=='events' || e.target.className=='drag-helper' ) {
              this.lastLayerY = e.layerY
          }

        this.currentDrag = this.getYPositionInCol(e)

        
          if(e.target.className=='ghost') {
              this.currentDrag += this.lastLayerY
          }

          this.ghost = this.startDragAt > this.currentDrag ? [this.convertYToHour(this.currentDrag), this.convertYToHour(this.startDragAt)] :[this.convertYToHour(this.startDragAt), this.convertYToHour(this.currentDrag)]
    
      },
      
      getHeight(timeBlockData){
          return (timeBlockData[1]-timeBlockData[0]) 
      },

      getY(hour){
          return hour - (this.minHour * 60)
      },

      

      openingTimes(){
        let opening_times = []
        for (let index = this.minHour; index < this.maxHour; index++) {
          opening_times.push(index+'h') 
        }
        return opening_times
      },
        
      deletedBlock( tblockid){
          let openedTimes = this.openedTimes
          openedTimes.splice(tblockid, 1)

          this.$emit('updatedSlots', this.daykey, openedTimes)
      },

      createBlock(event){

            this.currentDrag = this.startDragAt = 0
            this.isDragging = false

            let openedTimes = this.openedTimes
            if(this.ghost.length == 0) {
                this.ghost = [this.convertYToHour(event.layerY), this.convertYToHour(event.layerY+this.heightUnit)]
            }

            openedTimes.push(this.ghost)
            this.$emit('updatedSlots', this.daykey, openedTimes)
            this.ghost = []
      },


      updatedBlock(tblockid, hstart, hend, original){
          let openedTimes = this.openedTimes
          original[0] = hstart
          original[1] = hend
          openedTimes[tblockid] = original
          this.hiddenTimes = true
          this.$emit('updatedSlots', this.daykey, openedTimes)
           this.$nextTick()
          this.hiddenTimes = false
          this.$nextTick() 
      },

      editBlock(tblockid, hstart, hend, tblock){
          this.$emit('editBlock', this.daykey, hstart, hend, tblock)
      },

      convertYToHour(val){
          let baseInMinutes = (this.minHour*60)
          let slot = Math.floor(val/this.heightUnit)
          let hourInMinutes = baseInMinutes + (slot * this.heightUnit)

          return hourInMinutes
      },

  } 
}
</script>
<style>
    .ghost{
        background-color: rgb(185, 206, 212);
        position: absolute;
        width: 100%;
        border-radius:.6rem;
    }
    .drag-helper{
        position: relative;
        z-index: 6;
    }
    .ghost-wrapper{
        position: relative;
        z-index: 5;
    }
    .events{
        position: absolute;
        z-index: 4;
        height: 100%;
        width: 100%;
    }
    .timeText{
        color:#fff;
        font-size: .8rem;
    }
    .columnTitle{
        font-size: .9rem;
        text-transform: capitalize;
        font-weight: normal;
        color:#acacac;
    }
     .day-column{
        min-width: 100px;
        margin: 0 1rem;
        text-align: center;
        width: 100%;
        padding-top: 38px;
    }
    .day-column .columnTitle{
        font-weight: bold;
    }
    @media (max-width: 769px) { 
        .columnTitle{
            font-size: .8rem;
        }

        .columnTitle .dashicons, .columnTitle .dashicons-before::before {
            display: inline-block;
            width: 14px;
            height: 14px;
            font-size: 14px;
            vertical-align: sub;
        }
    }
</style>