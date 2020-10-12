<template> 
    <div class="hours-col"  @mouseover="editTimes" @mouseout="cancelEditTimes">
        <strong class="columnTitle">Hours <span class="dashicons dashicons-edit" ></span></strong>
        
        <div v-if="edittime" class="d-flex justify-content-center commands-hours commands-top">
            <button data-tt="Show less hours" class="btn btn-secondary btn-xs" @click="removeMin">-</button>
            <div  data-tt="Change precision each 10min, 20min etc...">
                <HoursDropdown :elements="durations" :current="precision" :funcDisplay="funcDisplay" @selected="changePrecision"/>
            </div>
            <button data-tt="Show more hours" @click="addMin" class="btn btn-secondary btn-xs">+</button>
        </div>

        <div :class="edittime ? 'box-shadow box-times active' : 'box-shadow box-times'">
            
            <div v-for="(time, timeidx) in openingTimes" class="hour-cell" >
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div v-for="interval in intervals" :class="{'hr-interval':interval !== intervals}" :style="'height: '+heightUnit+'px;'"></div>
                    </div>
                    <div>{{ time }}</div>
                    <div>
                        <div v-for="interval in intervals" :class="{'hr-interval':interval !== intervals}" :style="'height: '+heightUnit+'px;'"></div>
                    </div>
                </div>
                
            </div>
            
        </div>

        <div  v-if="edittime" class="d-flex justify-content-center commands-hours commands-bottom">
            <button data-tt="Show less hours" class="btn btn-secondary  btn-xs" @click="removeMax">-</button>
            <div  data-tt="Change precision each 10min, 20min etc...">
                <HoursDropdown :elements="durations" :current="precision" :funcDisplay="funcDisplay" @selected="changePrecision"/>
            </div>
            <button data-tt="Show more hours" @click="addMax" class="btn btn-secondary  btn-xs">+</button>
        </div>
    </div>
</template>


<script>
import HoursDropdown from '../Fields/ButtonMenu'
export default {
    props: ['openingTimes', 'heightUnit', 'precision'],
    components:{ HoursDropdown },
    data() {
        return {
            edittime: false,
            durations: [10, 15, 20, 30, 60],
        }
    },
    computed: {
        intervals(){
            return 60/this.precision
        }
    },
    methods: {
        changePrecision(duration){
            console.log('changing',duration)
            this.$emit('changedPrecision', duration)
        },
        funcDisplay(element){
            return element + 'min'
        },
        editTimes(){
            this.edittime = true;
        },
        cancelEditTimes(){
            this.edittime = false;
        },
        addMin(){
            this.$emit('addMin')
        },
        addMax(){
            this.$emit('addMax')
        },
        removeMin(){
            this.$emit('removeMin')
        },
        removeMax(){
            this.$emit('removeMax')
        }
  } 
}
</script>
<style>
    .hours-col{
        padding: 0 .5rem;
        min-width: 80px;
        box-shadow: 0 .2rem 1rem 0 rgba(0,0,0,.08);
    }

    .hour-cell{
        position: relative;
    }
    .box-times > div{
        text-align: center;
        padding: 0;
        font-size: .9rem;
        font-weight: normal;
        color:#727272;
    }
    .hr-interval{
        border-bottom: 1px solid #ccc;
        width: 6px;
    }
    .box-times .hour-cell{
        border-bottom: 1px solid #f3f3f3;
    }
    .commands-hours{
        position: absolute;
        border-radius: 6px;
        padding: .4em;
        background-color: #fff;
        left: -5px;
        box-shadow: 0 .2rem 1rem 0 rgba(0,0,0,.08);
    }
    .commands-top{
        top: 30px;
    }
    .commands-bottom{
        bottom: -30px;
    }
</style>
