<template>
    <select :class="classN" :id="id" @change="changed" v-model="model">
        <option v-for="day in weekdays" :value="day.i">{{ day.label }}</option>
    </select>
</template>

<script>
export default {
  props: ['id','selected', 'classN'],
  data() {
    return {
        model: 1,
        weekdays: {
            monday :{ label: 'Monday', i: 1},
            tuesday :{ label: 'Tuesday', i: 2},
            wednesday :{ label: 'Wednesday', i: 3},
            thursday :{ label: 'Thursday', i: 4},
            friday :{ label: 'Friday', i: 5},
            saturday :{ label: 'Saturday', i: 6},
            sunday :{ label: 'Sunday', i: 0},
        }
    }
  },
  created(){
      this.model = this.selected
  },
  methods: {
      changed(){
          this.$emit('changed', this.model)
      },
  },

}
</script>

