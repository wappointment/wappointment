<template>
    <input class="form-control" type="text" :value="value" :placeholder="valueInit" @input="changeInput" >
</template>

<script>
export default {
    props: ['value'],
    data: () => ({
        valueInit: '',
    }),
    created(){
       this.valueInit = this.value
    },
    methods: {
        changeInput(e){
            return this.$emit('input', e.target.value)
        }
    }
}   
</script>