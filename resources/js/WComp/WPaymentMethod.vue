<template>
    <div class="wtab" :class="{active: isActive }" @click="$emit('click', method.key)" role="button">
        <WImage :image="getImage(method.key,'.png')" />
    </div>
</template>

<script>

import WImage from './WImage'
import GetImage from '../Mixins/GetImage'
export default {
    components:{WImage},
    mixins: [GetImage],
    props:{
      method:{
        type: Object
      },
      active:{
        type: String
      }
    },
    computed: {
      isActive(){
        return this.active == this.method.key
      }
    },
}
</script>
<style >

.wtab{
  max-width: 34%;
  padding: .2em;
  border: 2px solid transparent;
  border-bottom: 0 !important;
  cursor: pointer;
}
.wtab.active{
  background-color: var(--wappo-body-bg);
  margin-bottom: -2px;
}
.wtab.active,
.wtab:hover{
  border-radius: .2em .2em 0 0;
  border: 2px solid var(--wappo-sec-bg);
}
.wtab img {
    filter: grayscale(1);
}
.wtab.active img,
.wtab:hover img{
  filter: grayscale(0);
}

</style>