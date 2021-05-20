<template>
    <div class="wtab" :class="{active: isActive }" @click="$emit('click', method.key)" role="button">
        <div class="d-flex justify-content-around">
          <WImage v-for="card in method.cards":image="getImage(card)" class="wstripe" :key="card"/>
        </div>
        <div class="d-flex wpowered align-items-center" v-if="method.desc">
          <span v-if="method.desc" >{{ method.desc }}</span> 
          <WImage v-if="method.logo" :image="getImage(method.key,'.png')" class="wstripe"/>
        </div>
    </div>
</template>

<script>

import WImage from './WImage'
export default {
    components:{WImage},
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
    methods: {
      getImage(method, ext = '.svg'){
        return {
          icon: 'methods/' + method + (method.indexOf('.') === -1 ? ext:''),
          alt: method,
          title: method
        }
      },
    },
}
</script>
<style >
.wstripe{
  max-width: 34px;
}
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
    max-height: 22px;
}
.wtab.active img,
.wtab:hover img{
  filter: grayscale(0);
}
.wpowered{
  font-size:10px;
}
.wpowered .img-fluid.wstripe[alt="paypal"] {
    max-height: 14px;
}
</style>