<template>
    <div class="wtabs d-flex">
        <WPaymentMethod v-if="methods.length > 1" v-for="method in methods" :key="method.key" :method="method" :tab="options.swift_payment[method.key+'_tab']"  :active="active" @click="clicked"/>
    </div>
</template>

<script>

import WPaymentMethod from './WPaymentMethod'
export default {
    props:['methods', 'options'],
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
      },

    }
}
</script>
