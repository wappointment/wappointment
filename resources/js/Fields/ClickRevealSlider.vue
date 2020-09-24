<template>
    <div>
        <a v-if="!revealed && !alwaysShow" href="javascript:;" @click="revealed=true"> {{ initvalue +' '+ definition.days }}</a>
        <FormFieldSlider v-else :value="initvalue" @change="changed" :definition="definition"/>
    </div>
</template>
<script>
import FormFieldSlider from '../Form/FormFieldDuration'
export default {
    props:['value','alwaysShow'],
    data() {
        return {
            revealed: false,
            initvalue: null,
            definition: {
                    min:1,
                    max: 120,
                    step:1,
                    int:true,
                    unit: 'days',
                    classWrapper: 'min-w200'
                }
        }
    },
    components: {FormFieldSlider},
    created(){
        this.initvalue = this.value
    },

    methods: {
        changed(val){
            this.$emit('change',val)
        }
    }
}
</script>
<style>
.min-w200{
    min-width: 200px;
}
</style>

