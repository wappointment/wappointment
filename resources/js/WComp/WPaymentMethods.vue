<template>
    <div class="wtabs d-flex">
        <WPaymentMethod v-if="methods.length > 1" v-for="method in methods" :key="method.key" :method="method" :active="active" @click="clicked"/>
    </div>
</template>

<script>

import WPaymentMethod from './WPaymentMethod'
export default {
    props:['methods'],
    components: { WPaymentMethod },
    data: () => ({
        active: '',
    }),
    created(){
      this.clicked(this.methods[0].key)
    },
    methods:{
      clicked(methodKey){
        this.active = methodKey
        this.$emit('selected', methodKey)
      }
    }
}
</script>
