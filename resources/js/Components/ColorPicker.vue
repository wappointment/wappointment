<template>
    <div class="wap-color-picker" :class="{'wapcpsmall': small}">
        <div class="d-flex">
            <div class="picker" :style="generateStyle" @click.stop="showPicker"></div>
            <div class="ml-1">{{ label }}</div>
        </div>
        <Chrome v-if="picker" :value="value" @input="getNewValue" v-click-outside="clickedOutside" ></Chrome>
    </div>
</template>

<script>

import ClickOutside from 'vue-click-outside'
import { Chrome } from 'vue-color'
import eventsBus from '../eventsBus'
export default {
    components: {
        Chrome
    },
    props: {
        value:{
            type:String
        },
        label:{
            type:String
        },
        small:{
            type:Boolean,
            default:false
        }
    },
    data: () => ({
        picker: false
    }),
    methods: {
        clickedOutside(){
            this.hidePicker()
        },
        hidePicker(){
            this.picker = false;
        },
        showPicker(){
            this.picker = true;
            eventsBus.emits('openedPicker', this.label)
        },
        getNewValue(val){
            this.$emit('input', val.hex)
        },
        openedPicker(labelOpened){
            if(this.label !== labelOpened){
                this.hidePicker()
            }
        }
    },
    created(){
        eventsBus.listens('openedPicker', this.openedPicker)
    },
    computed: {
        generateStyle(){
            return 'background-color:'+this.value;
        }
    },
    directives: {
        ClickOutside
    }
}
</script>
<style>
.wap-color-picker .picker{
    height: 1.4rem;
    width: 1.4rem;
    background-color: #ccc;
    border-radius: 4px;
    border: 1px solid #c3c3c3;
    cursor: pointer;
}
.wap-color-picker .picker:hover{
    box-shadow: 0px 0px 6px rgba(0,0,0,0.2);
}
.wap-color-picker .vc-chrome {
    background-color: rgb(255, 255, 255);
    border-radius: 0 40px 40px 40px;
    box-shadow: 0 0 9px rgba(0,0,0,.1);
    border: 1px #e7e7e7 solid;
    overflow: auto;
    position: absolute;
    z-index: 9;
}
.wap-color-picker.wapcpsmall .picker{
    height: 1rem;
    width: 1rem;
}
.wap-color-picker.wapcpsmall .ml-1{
    font-size:.8rem;
}
</style>
