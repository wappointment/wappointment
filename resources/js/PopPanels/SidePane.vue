<template>
    <div id="wappointmentactionbar">
        <div class="progress">
            <div class="progress-bar" role="progressbar" :style="'width:'+((step/total)*100)+'%'"></div>
        </div>
        <div class="buttons">
            <button class="button-primary button-large" @click="emitClick">Suivant</button>
        </div>
      </div>
</template>

<script>
export default {
  props: {
      step:0,
      total:0
  },
  methods: {
      emitClick(){
          this.$emit('next')
      },
     
  },

}
</script>
<style>
#wappointmentactionbar {
  width: 200px;
  position: fixed;
  top: 30px;
  bottom: 0;
  right: 0;
  z-index: 1000;
  padding: 20px;
  padding-right: 20px;
  padding-left: 20px;
  padding-right: 20px;
  padding-left: 20px;
  overflow-x: hidden;
  overflow-y: auto;
  border-right: 1px solid #eee;
  background-color: #fff;
  box-shadow: 0 .4rem 1rem 0 rgba(0,0,0,.1);
}
.progress {
    transition: all 1s ease-in-out;
    display: flex;
    height: 1rem;
    overflow: hidden;
    font-size: .75rem;
    background-color: #e9ecef;
    border-radius: .25rem;
}

.progress-bar {
    display: flex;
    flex-direction: column;
    justify-content: center;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    background-color: #007bff;
    transition: width .6s ease;
}
</style>