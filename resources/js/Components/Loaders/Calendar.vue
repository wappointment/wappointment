<template>
    <div class="lwrap">
        <div class="wap-l" :class="cName">
            <div v-for="n in nItems" :style="getStyle(n)"></div>
        </div>
    </div>
</template>

<script>
export default {
    data: () => ({
        nItems: 9,
        cName: ''
    }),
    methods: {
      getStyle(n) {
        return 'animation-delay:' + this.calculateDelay(n) + 's;'
      },
      calculateDelay(n){
        return (n*.05)
      }
    }
}
</script>
<style>

.wap-l {
  display: flex;
  width: 54px;
  height: 54px;
  flex-wrap: wrap;
  margin: 0 auto;
}

.wap-l > div {
  width: 16px;
  height: 16px;
  margin-right:2px;
  margin-bottom: 2px;
  background: #dedede;
  transform: scale(0);
  transform-origin: center center;
  animation: l .5s infinite cubic-bezier(.6,.01,.4,1);
  border-radius: 5px;
  opacity: 0;
}

@keyframes l {
  0% {
    transform: scale(1);
    opacity: 1;
    background-color: #dedede;
  }

  50% {
    transform: scale(0);
    opacity: 0;
    background-color: #6664cb;
  }
  100% {
    transform: scale(1);
    opacity: 1;
    background-color: #dedede;
  }
  
}

</style>
