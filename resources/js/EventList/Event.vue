<template>
  <div class="wevent">
    <div>{{ unixToDateTime(event[0], tz) }}</div>
    <div>{{ slotsLeft }} <button class="wbtn wbtn-primary wbtn-block" @click="selectEvent">Book</button></div>
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
  methods:{
    selectEvent(){
      this.$emit('selectEvent', {
        duration: this.event[1] - this.event[0],
        edit_key:this.event[4],
        end:this.event[1],
        left:this.event[2],
        llave:"grp",
        service:this.event[3],
        slots:1,
        start:this.event[0],
      })
    }
  }
}
</script>
<style>
.wevent{
  margin: 0.2em;
  padding: 0.3em;
  border: 2px solid #ececec;
  border-radius: 0.2em;
  background-color: #f2f2f2;
}
</style>
