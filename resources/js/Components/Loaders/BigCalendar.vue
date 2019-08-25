<template>
    <div class="lwrap">
        <div class="d-flex justify-content-around">
          <div class="binding"></div>
          <div class="binding"></div>
        </div>
        <div class="top-bar"></div>
      <div class="wap-l" :class="cName">
          <div v-for="n in nItems" :style="getStyle(n)"></div>
      </div>
    </div>
</template>

<script>
import CalendarL from './Calendar'
export default {
    extends: CalendarL,
    data: () => ({
        nItems: 31,
        cName: 'wap-cal'
    }),
    methods: {
      calculateDelay(n){
        return (n*.05) - (Math.trunc((n-.05)/5))*.1
      }
    }
}
</script>
<style>
.smallcc{
  font-size: 10px;
  text-align: center;
}
.lwrap {
  width: auto;
  margin: 0 auto;
  max-width: 200px;
}
.wap-l.wap-cal {
  width: 100%;
  height: 150px;
  margin: 10px auto;
}
.wap-l.wap-cal > div {
  margin-left: 16px;
}
.wap-l > div {
  margin-right: 0;
}
.top-bar{
  width: 100%;
  background-color: #dedede;
  height: 10px;
  border-radius: .4rem;
  transform: scale(0);
  transform-origin: center;
  animation: tbind 1s infinite cubic-bezier(1,0,0,1);
  border-radius: 5px;
  opacity: 1;
  margin: 1rem 1rem 1rem 0;
}

.binding {
  animation-delay: 0.05s;
  width: 24px;
  height: 34px;
  margin-bottom: 2px;
  background: #dedede;
  transform: scale(0);
  transform-origin: center center;
  animation: tbind 1s infinite cubic-bezier(0,.01,.4,1);
  animation-delay: 0s;border-radius: 5px;opacity: 1;
}
@keyframes tbind {
  0% {
    transform: scale(1);
    opacity: 1;
  }

  50% {
    transform: scale(0);
    opacity: 0;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}
</style>
