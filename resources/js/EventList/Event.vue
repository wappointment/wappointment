<template>
  <div class="wevent">
    <div>{{ unixToDateTime(event[0], tz) }}</div>
    <div>{{ slotsLeft }} <button class="wbtn wbtn-primary" @click="$emit('selectEvent', event)">Book</button></div>
  </div>
</template>
<script>
import Dates from '../Modules/Dates'
export default {
  mixins:[Dates],
  props:['event', 'buffer', 'tz', 'options'],
  computed:{
    slotsLeft(){
      return this.options.selection.title.replace('[total_slots]', this.event[2])
    },
  },
}
</script>
<style>
.wevent{
  padding: 0.2em;
  border-bottom: 1px solid #ccc;
}
</style>
