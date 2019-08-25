<template>
  <div class="regular-availability row">
    <template v-for="(openedTimes, daykey) in openedDays">
        <div v-if="insertHourColumn(daykey) && daykey!='monday'"  class="col-12 d-block d-sm-none">
            <hr >
        </div>
        <hourColumn v-if="insertHourColumn(daykey)" :heightUnit="heightUnit" :hideOnDesktop="daykey!=='monday'" :openingTimes="openingTimes" @addMin="addMin" @addMax="addMax" @removeMin="removeMin" @removeMax="removeMax"></hourColumn>
        <dayColumn  :key="daykey" :class="classColumn" :heightUnit="heightUnit" :daykey="daykey" :openedTimes="openedTimes" :minHour="minHour" :maxHour="maxHour" @updatedSlots="updatedSlots"></dayColumn>
    </template>
</div>
</template>

<script>
import dayColumn from '../Components/DayColumn'
import hourColumn from '../Components/HourColumn'
import orderBy from 'lodash/orderBy';
export default {
    props: ['initValue'],
    data() {
        return {
            classColumn: 'col-4 col-sm',
            notchSize: 1,
            minHour: 7,
            maxHour: 21, 
            heightUnit: 50,
            openedDays: {},
            timezone: {}
        }
    },
    components: { dayColumn, hourColumn }, 
    computed:{
        openingTimes(){
            let opening_times = []
            for (let index = this.minHour; index < this.maxHour; index++) {
                opening_times.push(index+'h') 
            }
            return opening_times
        },
    },
    created(){
        this.openedDays = this.initValue
        this.setMinAndMax()
         
    },
    methods: {
        insertHourColumn(dayname){
            if(['monday', 'wednesday', 'friday', 'sunday'].indexOf(dayname)!== -1) return true
        },
        setMinAndMax(){
            
            for (var property1 in this.openedDays) {
                let timeBlocks = this.openedDays[property1];

                for (let index = 0; index < timeBlocks.length; index++) {
                    let timeblock = timeBlocks[index];
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
            let timeBlocks = this.openedDays[property1];

            for (let index = 0; index < timeBlocks.length; index++) {
                let timeblock = timeBlocks[index];
                if(timeblock[0]<this.minHour){
                    hasChanged=true
                    timeblock[0]=this.minHour
                } 
                if(timeblock[1]>this.maxHour){
                    hasChanged=true
                    timeblock[1]=this.maxHour
                } 
                if(timeblock[0] == timeblock[1]) this.openedDays[property1].splice(index,1)
                else this.openedDays[property1][index] = timeblock
            }
        }

        if(hasChanged) this.updatedRegav()

     },
      
      
      updatedSlots(daykey, tblockid, hstart, hend){
          let openedDays = this.openedDays;
          openedDays[daykey][tblockid] = [hstart, hend];

          this.openedDays = [];
          this.openedDays = openedDays;

          this.refreshTimeBlocks(daykey)
      },


      refreshTimeBlocks(daykey){
        let orderedres = this.getSeriePerDay(daykey)
        let openedDays = this.openedDays
        this.openedDays = []
        openedDays[daykey] = this.makeTimeBlocks(orderedres)  
        
        this.openedDays = openedDays
        this.updatedRegav()
      },
      updatedRegav(){
          this.$emit('updatedDays',this.openedDays)
      },

      getSerie(timeBlock){
          var result = [];
          for (var i = timeBlock[0]; i != timeBlock[1]; ++i) result.push(i)
          return result
      },

      getSeriePerDay(daykey){
          var result = [];

          for (let index = 0; index < this.openedDays[daykey].length; index++) {
              
              result =[...new Set([...result, ...this.getSerie(this.openedDays[daykey][index])])]

          }
        return orderBy(result)

      },

      makeTimeBlocks(serie){
          let serieIndex = 0
          let newSeries = []
          let elementNext = -1
          for (let index = 0; index < serie.length; index++) {
              let element = serie[index]
              if(elementNext !== -1 && (elementNext + this.notchSize) != element) {
                  serieIndex++
              }
              if(newSeries[serieIndex] === undefined) newSeries[serieIndex] = [];
              newSeries[serieIndex].push(element)
              elementNext = element
          }
          
          let timeBlocks = [];
          for (let index = 0; index < newSeries.length; index++) {
              let newBlock = [];
              newBlock.push(newSeries[index][0])
              newBlock.push(newSeries[index].pop() + this.notchSize);
              timeBlocks.push(newBlock)
          }
         return timeBlocks;
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
        border: 2px dashed #f2f2f2;
        background-color: rgb(242, 242, 242);
        transition: transform .3s ease-in-out;
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
        width: 100%;
        height: 20px;
        left: 0;
        margin-left: 0;
        border-radius: 8px;
        color: #888;
        background-color: rgba(0,0,0,.1);
        border: none;
        text-align: center;
    }

    .box-shadow .draggable .handle:hover{
        background-color: rgba(15, 15, 15, 0.3);
    }

    .box-shadow .draggable .handle-tm {
        top: 0px;
    }
    .box-shadow .draggable .handle-bm {
        bottom: 0px;
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

    @media (max-width: 769px) { 
        .regular-availability .col-sm{
            max-width: 34%;
        }
    }
    
    
</style>