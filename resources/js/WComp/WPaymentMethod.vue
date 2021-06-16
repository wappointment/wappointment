<template>
    <div class="wtab" :class="[isActive?'active':'inactive']" @click="$emit('click', method.key)" role="button">
        <WImage v-if="icon" :image="getImage(method.key,'.png')" />
        <span class="wmethod-name">{{method.desc}}</span>
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
      },
      icon:{
        type: Boolean,
        default:false
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

.wap-front .wtab,
.wap-front .wrap-calendar div.wtab{
  font-size: 14px;
  margin-right: .4em;
  padding: .8em;
  border: 2px solid var(--wappo-sec-bg);
  border-bottom: 0 !important;
  cursor: pointer;
  background-color:var(--wappo-body-bg);
  color: var(--wappo-sec-tx);
  border-radius:.4em .4em 0 0;
  font-weight: bold;
}
.wap-front .wtab.active, 
.wap-front .wrap-calendar div.wtab.active{
  background-color: var(--wappo-sec-sel-bg);
  color: var(--wappo-sec-sel-tx);
  margin-bottom: -2px;
}
.wap-front .wtab.active,
.wap-front .wtab:hover{
  border-radius: .4em .4em 0 0;
  border: 2px solid var(--wappo-sec-bg);
}

.wap-front .wtab.active:hover{
  background-color: var(--wappo-sec-bg);
}
.wtab img {
    filter: grayscale(1);
    max-width: 70px;
}
.wtab.active img,
.wtab:hover img{
  filter: grayscale(0);
}

</style>