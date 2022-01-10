
<template>
    <div class="d-flex items-align-center sm-text">
        <a v-if="!viewingFreeSlot" @click="$emit('getFreeSlots')" 
        class="tt-below d-none d-md-block ml-2 align-self-center" 
        :data-tt="get_i18n('show_free_slots','calendar')" href="javascript:;">
        {{ sprintf_i18n('free_slots','calendar', totalSlots)}}</a>
        <a v-else class="tt-below align-self-center" href="javascript:;" @click="$emit('getEdition')">{{ sprintf_i18n('back','common', totalSlots)}}</a>
        <div class="dropdown ml-2 d-flex align-self-center" :class="{'show': toggle}" v-if="durations.length > 1">
            <button class="btn btn-secondary btn-light dropdown-toggle btn-xs" type="button" @click="toggle=!toggle">
                {{ sprintf_i18n('regav_min','common', selectedDuration)}} 
            </button>
            <div class="dropdown-menu" :class="{'show': toggle}">
                <a class="dropdown-item" href="javascript:;" v-for="durationI in durations" @click="selectDuration(durationI)"> 
                    {{ sprintf_i18n('regav_min','common', durationI)}}</a>
            </div>
            
        </div>
        <div v-else class="ml-2 align-self-center text-muted">
            {{ sprintf_i18n('regav_min','common', selectedDuration)}} 
        </div>
    </div>
</template>

<script>
export default {
    props: ['intervals', 'viewingFreeSlot', 'durations',  'buffer'],
    data: () => ({
        toggle: false,
        selectedDuration: 0,
    }),
    created(){
        this.selectedDuration = this.durations[0]
    },
    methods:{
        selectDuration(duration){
            this.selectedDuration = duration
            this.toggle = false
        }
    },
    computed: {
        totalSlots() {
            return this.intervals === 0 ? 0:this.intervals.splits(parseInt(this.selectedDuration)*60).totalSlots()
        },
    }
}
</script>