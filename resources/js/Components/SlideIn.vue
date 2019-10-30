<template>
  <div>
      <div v-if="!show_already" class="wappo-slide-in" :class="{show: appear && disappear!==true, 'd-flex align-items-center':flex}"   :data-route="routerPath">
        <button class="btn btn-white" @click="check"><slot name="intro" ></slot></button>
      </div>
      <WapModal :screenshot="true" :show="show" @hide="hideModal">
          <h4 slot="title" class="modal-title"><slot name="modal-title"></slot></h4>
          <slot name="modal-body"></slot>
    </WapModal>
  </div>
</template>
<script>
export default {
    props:{
        flex:{
            type:Boolean,
            default: true
        },
        disappearOnCheck:{
            type:Boolean,
            default: true
        },
        show_already:{
            type:Boolean,
            default: false
        }
    },
    data: () => ({
        show: false,
        appear: false,
        init: true,
        disappear: false
    }),

    computed:{
        routerPath(){
            if(!this.init) {
                this.appear = false
                setTimeout(this.appearNow, 1000)
            }
            
            return this.$route.path
        },
    },
    mounted(){
        if(this.show_already){
            this.show = true
        }else{
            this.init = false
            setTimeout(this.appearNow, 2000)
        }
      
    },
    methods:{
        appearNow(){
            this.appear = true
        },
        check(){
            this.show = true
            this.$emit('viewed')
            if(this.disappearOnCheck === true) this.disappear = true
        },
        hideModal(){
            this.show = false
            this.$emit('closed')
        },
    }
}
</script>
<style >
    .wappo-slide-in {
        position: fixed;
        bottom: -200px;
        z-index: 9;
        border-radius: 1rem 1rem 0 0;
        background-color: #fff;
        border-bottom: 0;
        box-shadow: 0 .4rem 1rem 0 rgba(0,0,0,.1);
        transition: all .5s ease-in-out;
        border: 1px solid #ededed;
        width: auto;
        right: 1rem;
        padding: 1rem;
        color: #6664cb;
    }
    .wappo-slide-in.show {
        bottom: 0;
    }
</style>
