<template>
    <div>
        <div class="d-flex w100">
            
            <div class="columnTitle" data-tt="Number of days in the future where you're made available">Available Booking Days</div> 
                <ClickRevealSlider :alwaysShow="true" 
                :value="viewData.availaible_booking_days" @change="changedCRS" />
            </div>
        <div class="commands-frame d-flex" @mouseover="showControls=true" @mouseout="showControls=false">
            
            <div class="scroll-top" v-if="isMounted && controlsShown">
                <div v-if="scrolledAtTheEnd" class="btn btn-link btn-xs" role="button" @click="scrollToStart"><< Start</div>
                <div v-if="widthWeekWrapper < 389 && !scrolledAtTheStart" role="button" class="btn btn-link btn-xs" @click="scrollToPrev">< Prev</div>
                <div v-if="widthWeekWrapper < 389 && !scrolledAtTheEnd" role="button" class="btn btn-link btn-xs" @click="scrollToNext">Next ></div>
                <div v-if="!scrolledAtTheEnd" class="btn btn-link btn-xs" role="button" @click="scrollToEnd">End >></div>
            </div>
            
            <hourColumn v-if="precision" 
            :heightUnit="precision" :openingTimes="openingTimes" :precision="precision"
            @addMin="addMin" @addMax="addMax" @changedPrecision="changedPrecision"
            @removeMin="removeMin" @removeMax="removeMax"
            ></hourColumn>
            <div id="regav-wrapper" ref="regavwrap" @scroll="scrolledTrigger" class="regular-availability d-flex" >
                <template v-for="(openedTimes, daykey) in openedDays">     
                    <dayColumn v-if="daykey!=='precise'"  :key="daykey" :class="classColumn" :heightUnit="precision" :daykey="daykey" 
                    :openedTimes="openedTimes" :minHour="minHour" :maxHour="maxHour" :precision="precision"
                    @updatedSlots="updatedSlots" @editBlock="editBlock"></dayColumn>
                </template>
            </div>
            <WapModal v-if="showCustomRegav" :show="showCustomRegav" @hide="hideCustomRegav" large>
                <h4 slot="title" class="modal-title" >Set conditions for that time ({{dayEdit}}  [{{ convertPrToh(timeEdit[0]) + 'h - ' +convertPrToh(timeEdit[1]) }}h])</h4>
                <WAPFormGenerator v-if="schemaRegavCondition" ref="fg-regavCondition" :schema="schemaRegavCondition" :data="modelHolder" 
                @submit="saveRegavCond" @back="hideCustomRegav" :errors="errorsPassed" :key="'regavCondForm'" 
                labelButton="Save" :backbutton="true" backbuttonLabel="Cancel" />
            </WapModal>
        </div>
    </div>
</template>

<script>
import dayColumn from './DayColumn'
import hourColumn from './HourColumn'
import ClickRevealSlider from '../Fields/ClickRevealSlider'
import orderBy from 'lodash/orderBy'

export default {
    props: ['initValue','viewData'],
    data() {
        return {
            classColumn: 'day-column',
            notchSize: 1,
            precision: 60,
            minHour: 7,
            maxHour: 21,
            openedDays: {},
            openedDaysConditional: {},
            timezone: {},
            showControls: false,
            leftValue: 0,
            scrollStep: 133,
            widthWeekWrapper: 0,
            isMounted: false,
            isSmallScreen: window.document.documentElement.clientWidth < 1100,
            showCustomRegav: false
        }
    },
    components: { dayColumn, hourColumn, ClickRevealSlider }, 
    created(){
        this.openedDays = this.initValue
        this.setMinAndMax()
    },
    mounted(){
        this.leftValue = this.$refs.regavwrap.scrollLeft
        this.widthWeekWrapper = this.$refs.regavwrap.clientWidth
        this.isMounted = true
    },
    computed:{

        controlsShown(){
            return this.isSmallScreen || this.showControls
        },
        scrolledAtTheStart(){
            return this.leftValue == 0
        },
        scrolledAtTheEnd(){
            let maxScrollLeft = this.$refs.regavwrap.scrollWidth - this.$refs.regavwrap.clientWidth
            return this.leftValue == maxScrollLeft
        },
        openingTimes(){
            let opening_times = []
            for (let index = this.minHour; index < this.maxHour; index++) {
                opening_times.push(index+'h') 
            }
            return opening_times
        },
        hourSplits(){
            return 60/this.precision
        }
    },
    
    methods: {
        changedPrecision(precision){
            this.precision = precision
        },
        changedCRS(value){
            this.$emit('changedABD', value)  
        },
        editBlock(){ // addons function

        },
        scrolledTrigger(){
            this.leftValue = this.$refs.regavwrap.scrollLeft
        },
        scrollToStart(){
            this.$refs.regavwrap.scrollLeft = 0
            this.leftValue = 0
        },
        scrollToPrev(){
            let ScrollTo = this.$refs.regavwrap.scrollLeft - this.scrollStep
            this.$refs.regavwrap.scrollLeft = ScrollTo
            this.leftValue = ScrollTo
        },
        scrollToNext(){
            let ScrollTo = this.$refs.regavwrap.scrollLeft + this.scrollStep
            this.$refs.regavwrap.scrollLeft = ScrollTo
            this.leftValue = ScrollTo
        },
        scrollToEnd(){
            let ScrollTo = this.$refs.regavwrap.scrollWidth - this.$refs.regavwrap.clientWidth
            this.$refs.regavwrap.scrollLeft = ScrollTo
            this.leftValue = ScrollTo
        },
        insertHourColumn(dayname){
            if(['monday', 'wednesday', 'friday', 'sunday'].indexOf(dayname)!== -1) return true
        },
        setMinAndMax(){
            
            for (var day in this.openedDays) {
                let timeBlocks = this.openedDays[day]
                if(Array.isArray(timeBlocks)){
                    for (let i = 0; i < timeBlocks.length; i++) {
                        const start_in_hour = this.convertPrToh(timeBlocks[i][0])
                        const end_in_hour = this.convertPrToh(timeBlocks[i][1])
                        if(start_in_hour < this.minHour){
                            this.minHour = start_in_hour > 0 ? start_in_hour - 1 :0
                        } 
                        if(end_in_hour > this.maxHour){
                            this.maxHour = end_in_hour < 24 ? end_in_hour + 1 :24
                        }
                    }
                }
            }
        },
      
      convertPrToh(minOrHour){
          return this.openedDays.precise === undefined ? minOrHour:Math.floor(minOrHour/60)
      },
      addMin(){
          if(this.minHour>0) {
              this.minHour--
              this.parseOpenedDays()
          }
      },
      removeMin(){
          this.minHour++
          this.parseOpenedDays()
      },
      addMax(){
          if(this.maxHour <24){
              this.maxHour++
            this.parseOpenedDays()
          }
      },
      removeMax(){
          this.maxHour--
          this.parseOpenedDays()
      },
      parseOpenedDays(){
        let hasChanged = false
        for (var property1 in this.openedDays) {
            let timeBlocks = this.openedDays[property1]

            for (let i = 0; i < timeBlocks.length; i++) {
                let timeblock = timeBlocks[i]
                if(timeblock[0] < this.minHour *60 ){
                    hasChanged = true
                    timeblock[0] = this.minHour *60 
                } 
                if(timeblock[1] > this.maxHour *60 ){
                    hasChanged = true
                    timeblock[1] = this.maxHour *60 
                } 
                if(timeblock[0] == timeblock[1]) {
                    this.openedDays[property1].splice(i,1)
                }else {
                    this.openedDays[property1][i] = timeblock
                }
            }
        }

        if(hasChanged) {
            this.updatedRegav()
        }

     },
      
      
      updatedSlots(daykey, blocks_values_for_day){

          let openedDays = this.openedDays
          
          openedDays[daykey] = blocks_values_for_day

          this.openedDays = []
          this.openedDays = openedDays

          this.refreshTimeBlocks(daykey)
      },


      refreshTimeBlocks(daykey){
          
        let openedDays = this.openedDays
        this.openedDays = []

        openedDays[daykey] = orderBy(this.cleanSlots(openedDays[daykey]))

        this.openedDays = openedDays
        this.updatedRegav()
      },

      updatedRegav(){
          this.$emit('updatedDays',this.openedDays)
      },

      cleanSlots(day_slots){
          let newDaySlots = []
          let merge = false
          for (let i = 0; i < day_slots.length; i++) {
              if(merge === false){ // we compare while there is no merge planed
                  for (let j = 0; j < day_slots.length; j++) {
                    if(j !== i){
                        //we compare the two timeblocks
                        if(!this.blocksDoNotTouch(day_slots[i], day_slots[j])){
                            //requires a merge and a cleanSlots restart
                            merge = [i,j]
                        }
                    }
                    
                }
              }

              if(merge === false || (merge !== false && merge.indexOf(i) === -1)){ // we insert only the slots that are not planned for merge
                newDaySlots.push(day_slots[i])
              }

          }
          if(merge !== false){ // we are mergine and pushing new item
              newDaySlots.push(this.mergeBlocks(day_slots[merge[0]], day_slots[merge[1]])) 
              return this.cleanSlots(newDaySlots)
          }
          return newDaySlots
      },

    mergeBlocks(block1, block2){
        let start = block1[0] <= block2[0] ? block1[0]:block2[0]
        let end = block1[1] >= block2[1] ? block1[1]:block2[1]
        return start < end ? [start, end]:[end, start]
    },

      blocksDoNotTouch(block1, block2){
          /*
          *           [--b1--]
          *[--b2--] 
          * or
          * *         [--b2--]
          *[--b1--]
          */
          return block1[1] < block2[0] || block2[1] < block1[0] 
      },


  } 
}
</script>
<style>
    .box-shadow{
        border: 2px solid #f3f3f3;
        position: relative;
        border-radius: .6rem;
    }
    .box-shadow .draggable{
        background-color: rgb(162, 195, 204);
        transition: transform .3s ease-in-out;
        padding: .5em;
    }
    .box-shadow .draggable:hover {
        cursor: grab;
    }
    .box-shadow .draggable.dragging {
        transform: scale(1.01);
    }
    .box-shadow .draggable.dragging:hover {
        cursor: grabbing;
    }

    .box-shadow .draggable .handle{
        width: 30%;
        height: 20px;
        left: 32px;
        margin-left: 0;
        color: #fff;
        background-color: rgb(134, 174, 185);
        border: none;
        text-align: center;
        margin: 0 auto;
    }
    .box-shadow .draggable .handle.handle-actions{
        left: 0;
        top: 7px;
        background: rgb(157, 166, 168);
        padding: .2em;
        color: #fff;
        border-radius: 6px;
        min-height : 30px;
        height:auto;
    }
    .box-shadow .draggable .handle-actions.handle-show {
        left: -26px;
    }
    .box-shadow .draggable .handle:hover{
        background-color: rgb(134, 174, 185, .8);
    }

    .box-shadow .draggable .handle-tm {
        border-radius: 6px 6px 0 0;
    }
    .box-shadow .draggable .handle-bm {
        border-radius: 0 0  6px 6px;
    }

    .box-shadow .draggable .handle-tm.handle-show {
        top: -20px;
    }
    .box-shadow .draggable .handle-bm.handle-show {
        bottom: -20px;
    }

    .box-shadow {
        transition: all .3s ease-in-out;
        box-shadow: 0 0 0 0 rgba(0,0,0,.1);
    }
    .box-shadow.active{
        border-color: #c5c5c5;
    }
    .regular-availability .col-sm{
        max-width: 13%;
        text-align: center;
    }

    .regular-availability  .col-sm-1{
        text-align: center;
    }
   
    .regular-availability{
        overflow-x: scroll;
        overflow-y: hidden;
    }
    .scroll-top{
        display:none;
    }
    .commands-frame{
        position: relative;
        padding-top:2rem;
    }
    @media (max-width: 1152px) { 
        .scroll-top{
            display:block;
            position: absolute;
            z-index: 2;
            top: 0;
            right: 0;
        }
        .scroll-top.scrolled-end{
            left:0;
            right:auto;
        }
        .regular-availability{
            scroll-behavior: smooth;
        }
    }
    
</style>