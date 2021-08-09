<template>
    <select :class="classN" :id="id" @change="changed" v-model="model">
        <option v-for="n in times" :value="n">{{ formatTime(n) }}</option>
    </select>
</template>

<script>

import convertDateFormatPHPtoMoment from '../Standalone/convertDateFormatPHPtoMoment'
import momenttz from '../appMoment'

export default {
  props: ['id','selected', 'classN', 'timeFormat'],
  data() {
    return {
        times:[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23],
        model: 1,
    }
  },
  created(){
      this.model = this.selected
  },
  methods: {
      formatTime(h){
          let formattedFormat = convertDateFormatPHPtoMoment(this.timeFormat)
          return momenttz().hours(h).minutes(0).format(formattedFormat)
      },
      changed(){
          this.$emit('changed', this.model)
      },
  },

}
</script>

