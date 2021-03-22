
<template>
    <div class="text-center">
        <div v-for="typeInput in typesAllowed" @click="selectType(typeInput)" role="button" 
        class="wbtn wbtn-secondary wbtn-cell mr-2" :class="{selected: isSelected(typeInput)}">
            <WapImage :faIcon="getTypeObject(typeInput).icon" size="md" />
            <div>{{ getTypeObject(typeInput).label }}</div>
        </div>
    </div>
</template>

<script>
import MixinTypeSelected from './MixinTypeSelected'
export default {
    mixins: [MixinTypeSelected],
    props:['options', 'typeSelected', 'typesAllowed'],
    mounted(){
        this.selection = this.typeSelected
    },

    methods:{
        isSelected(type){
            return this[type+'Selected']
        },
        selectType(type){
            this.selection = type
            this.$emit('selectType',type)
        },
        allowedType(type){
            return this.typesAllowed.indexOf(type) !== -1
        },
        getTypeObject(type){
            switch (type) {
                case 'zoom':
                    return {
                        label: this.options.form.byzoom,
                        icon: ['fas', 'video']
                    }
                case 'physical':
                    return {
                        label: this.options.form.inperson,
                        icon: 'map-marked-alt'
                    }
                case 'phone':
                    return {
                        label: this.options.form.byphone,
                        icon: 'phone'
                    }
                case 'skype':
                    return {
                        label: this.options.form.byskype,
                        icon: ['fab', 'skype']
                    }
            }
        }
    }
}
</script>