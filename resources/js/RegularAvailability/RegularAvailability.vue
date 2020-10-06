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
                    <dayColumn  :key="daykey" :class="classColumn" :heightUnit="precision" :daykey="daykey" 
                    :openedTimes="openedTimes" :minHour="minHour" :maxHour="maxHour" :precision="precision"
                    @updatedSlots="updatedSlots" @editBlock="editBlock"></dayColumn>
                </template>
            </div>
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
            isSmallScreen: window.document.documentElement.clientWidth < 1100
            
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
                /* for (let j = 0; j < this.hourSplits; j++) {
                    //const element = this.hourSplits[j];
                    opening_times.push(index+'h'+(j*this.precision)+'min') 
                } */
                
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
        editBlock(){

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
            
            for (var property1 in this.openedDays) {
                let timeBlocks = this.openedDays[property1]

                for (let index = 0; index < timeBlocks.length; index++) {
                    let timeblock = timeBlocks[index]
                    if(timeblock[0] < this.minHour){
                        if(timeblock[0]>0) this.minHour = timeblock[0] - 1
                        else this.minHour = 0
                    } 
                    if(timeblock[1] > this.maxHour){
                        if(timeblock[1]<24) this.maxHour = timeblock[1] + 1
                        else this.maxHour = 24
                    }
 
                }
            }
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
        let hasChanged=false
        for (var property1 in this.openedDays) {
            let timeBlocks = this.openedDays[property1]

            for (let index = 0; index < timeBlocks.length; index++) {
                let timeblock = timeBlocks[index]
                if(timeblock[0]<this.minHour *60 ){
                    hasChanged=true
                    timeblock[0]=this.minHour *60 
                } 
                if(timeblock[1]>this.maxHour *60 ){
                    hasChanged=true
                    timeblock[1]=this.maxHour *60 
                } 
                if(timeblock[0] == timeblock[1]) this.openedDays[property1].splice(index,1)
                else this.openedDays[property1][index] = timeblock
            }
        }

        if(hasChanged) this.updatedRegav()

     },
      
      
      updatedSlots(daykey, tblockid){

          let openedDays = this.openedDays
          openedDays[daykey] = tblockid

          this.openedDays = []
          this.openedDays = openedDays

          this.refreshTimeBlocks(daykey)
      },


      refreshTimeBlocks(daykey){
          
        let orderedres = this.getSeriePerDay(daykey)
        let openedDays = this.openedDays
        this.openedDays = []
        let original = openedDays[daykey]

        openedDays[daykey] = this.makeTimeBlocks(orderedres)
        for (let i = 0; i < original.length; i++) {
            if(original[i].length > 2){ // check for extra params on that regav
                for (let j = 0; j < openedDays[daykey].length; j++) {
                    if(openedDays[daykey][j][0] == original[i][0] && openedDays[daykey][j][1] == original[i][1]){
                        if(openedDays[daykey][j][2] === undefined) {
                            openedDays[daykey][j].push(original[i][2])
                        }else{
                            openedDays[daykey][j][2] == original[i][2]
                        }
                        
                    }
                }
            }
        }

        this.openedDays = openedDays
        this.updatedRegav()
      },

      updatedRegav(){
          this.$emit('updatedDays',this.openedDays)
      },

      getSerie(timeBlock){
          var result = []
          for (var i = timeBlock[0]; i != timeBlock[1]; i = i + this.precision ) result.push(i)
          console.log('result',result)
          alert('exit')
          return result
      },

      getSeriePerDay(daykey){
          var result = []

          for (let index = 0; index < this.openedDays[daykey].length; index++) {
              result =[...new Set([...result, ...this.getSerie(this.openedDays[daykey][index])])]
          }
          return orderBy(result)

      },
      
      /**
       * convert a serie to an aray of start-end e.g.:[9,10,11,15,16,17] => [[9,11],[15,17]]
       */
      makeTimeBlocks(serie){
          let serieIndex = 0
          let newSeries = []
          let elementNext = -1
          for (let index = 0; index < serie.length; index++) {
              let element = serie[index]
              if(elementNext !== -1 && (elementNext + this.notchSize) != element) {
                  serieIndex++
              }
              if(newSeries[serieIndex] === undefined) newSeries[serieIndex] = []
              newSeries[serieIndex].push(element)
              elementNext = element
          }
          
          let timeBlocks = [];
          for (let index = 0; index < newSeries.length; index++) {
              let newBlock = []
              newBlock.push(newSeries[index][0])
              newBlock.push(newSeries[index].pop() + this.notchSize);
              timeBlocks.push(newBlock)
          }
         return timeBlocks
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
        padding: 0;
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

    .box-shadow .draggable .handle:hover{
        background-color: rgb(134, 174, 185, .8);
    }

    .box-shadow .draggable .handle-tm {
        top: -20px;
        border-radius: 6px 6px 0 0;
    }
    .box-shadow .draggable .handle-bm {
        bottom: -20px;
        border-radius: 0 0  6px 6px;
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