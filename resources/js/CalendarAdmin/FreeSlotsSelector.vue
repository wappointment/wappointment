
<template>
    <div class="d-flex items-align-center sm-text">
        <a v-if="!viewingFreeSlot" @click="$emit('getFreeSlots')" 
        class="tt-below d-none d-md-block ml-2 align-self-center" 
        data-tt="Show free times" href="javascript:;">{{ totalSlots }} Free slots</a>
        <a v-else class="tt-below align-self-center" href="javascript:;" @click="$emit('getEdition')">Back to edition</a>
        <div class="dropdown ml-2 d-flex align-self-center" :class="{'show': toggle}" v-if="durations.length > 1">
            <button class="btn btn-secondary dropdown-toggle btn-xs" type="button" @click="toggle=!toggle">
                {{duration}}min
            </button>
            <div class="dropdown-menu" :class="{'show': toggle}">
                <a class="dropdown-item" href="javascript:;" v-for="durationI in durations" @click="selectDuration(durationI)"> {{durationI}}min</a>
            </div>
            
        </div>
        <div v-else class="ml-2 align-self-center text-muted">
            {{duration}}min
        </div>
    </div>
</template>

<script>
export default {
    props: ['totalSlots', 'viewingFreeSlot', 'durations', 'duration'],
    data: () => ({
        toggle: false
    }),
    methods:{
        selectDuration(duration){
            
            this.$emit('resizeSlots', duration)
            this.toggle = false
        }
    }
}
</script>
<style>
.sm-text{
    font-size:.9em;
}
</style>