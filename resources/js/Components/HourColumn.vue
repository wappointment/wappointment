<template> 
    <div class="hours-col"  @mouseover="editTimes" @mouseout="cancelEditTimes">
        <strong class="columnTitle">Hours <span class="dashicons dashicons-edit" ></span></strong>
        <div :class="edittime ? 'box-shadow box-times active' : 'box-shadow box-times'">

            <div v-for="(time, timeidx) in openingTimes" class="hour-cell" :style="'height: '+heightUnit+'px;'">
                <div class="controltime-tp" v-if="timeidx == 0">
                    <div v-if="edittime" class="d-flex justify-content-between incdec">
                        <button data-tt="Show less hours" class="btn btn-secondary btn-xs" @click="removeMin">-</button>
                        <button data-tt="Show more hours" @click="addMin" class="btn btn-secondary btn-xs">+</button>
                    </div>
                </div>
                {{ time }} 
                <div class="controltime-bt" v-if="timeidx == openingTimes.length - 1">
                    <div  v-if="edittime" class="d-flex justify-content-between incdec">
                        <button data-tt="Show less hours" class="btn btn-secondary  btn-xs" @click="removeMax">-</button>
                        <button data-tt="Show more hours" @click="addMax" class="btn btn-secondary  btn-xs">+</button>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</template>

<script>
export default {
    props: ['openingTimes','heightUnit'],
    data() {
        return {
            edittime: false
        }
    },
    created(){
//this.heightUnit = this.hu
    },
    methods: {
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
    .controltime-bt, .controltime-tp{
        z-index: 1;
        left: calc(50% - 22px);
        position: absolute;
    }
    .controltime-tp{
        top: -10px;
    }
    .controltime-bt{
        top: 37px;
    }
    .hour-cell{
        position: relative;
    }
    .box-times > div{
        text-align: center;
        padding: 1rem 0;
        font-size: .9rem;
        font-weight: normal;
        color:#727272;
        
    }
    .box-times .hour-cell{
        border-bottom: 1px solid #f3f3f3;
    }
    .incdec{
        left: 7px;
        position: absolute;
    }
    @media (max-width: 769px) { 
        .controltime-tp{
            top: -40px;
        }
    }

</style>
