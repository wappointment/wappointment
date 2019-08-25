<template>
    <div class="wap-l wap-r"></div>
</template>

<script>
export default {

}
</script>
<style>
.wap-l.wap-r {
  min-width: 54px;
  min-height: 54px;
  border-radius: 50px;
  box-shadow: 0 2px 0 #dedede inset;
  animation: r 1s cubic-bezier(.6,.01,.4,1) infinite;
  margin: 0 auto;
}

@keyframes r {
    0% {
        transform: rotate(0);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
