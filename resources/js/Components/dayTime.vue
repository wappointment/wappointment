<template>
    <select :class="classN" :id="id" @change="changed" v-model="model">
        <option v-for="n in 23" :value="n">{{ formatTime(n) }}</option>
    </select>
</template>

<script>

import convertDateFormatPHPtoMoment from '../Standalone/convertDateFormatPHPtoMoment'
import momenttz from '../appMoment'

export default {
  props: ['id','selected', 'classN', 'timeFormat'],
  data() {
    return {
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

