
<template>
    <div class="d-flex items-align-center">
        <span v-if="!viewingFreeSlot" @click="$emit('getFreeSlots')" 
        class="tt-below d-none d-md-block badge badge-secondary align-self-center ml-3 btn btn-link" 
        data-tt="View free slots">Free slots {{ totalSlots }}</span>
        <span v-else class="tt-below btn btn-link" @click="$emit('getEdition')">Back to edition</span>
        <div class="dropdown ml-2 d-flex align-items-center" :class="{'show': toggle}" v-if="durations.length > 0">
            <button class="btn btn-secondary dropdown-toggle btn-xs" type="button" @click="toggle=!toggle">
                {{duration}}min
            </button>
            <div class="dropdown-menu" :class="{'show': toggle}">
                <a class="dropdown-item" href="javascript:;" v-for="durationI in durations" @click="selectDuration(durationI)"> {{durationI}}min</a>
            </div>
            
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