<template>
    <div class="wap-front" :class="{'br-fixed': isBottomRight}" :id="elementId">
        <StyleGenerator :options="opts" :wrapper="elementId"></StyleGenerator>
        <div v-if="isPage">
            <BookingFormDemo v-if="isBookingPage" :options="opts"></BookingFormDemo>
            <ViewingAppointment v-else  :options="opts" :view="getParameterByName('view')" :appointmentkey="getParameterByName('appointmentkey')"></ViewingAppointment>
        </div>
        
        <div class="wap-wid" v-if="isWidget">
            <span v-if="bookForm && isBottomRight" @click="backToButton" class="close-wid"></span>
            <BookingFormDemo v-if="bookForm" :step="currentStep" :options="opts" :passedDataSent="dataSent"></BookingFormDemo>
            <BookingButton v-else @click="toggleBookForm" class="wbtn wbtn-booking wbtn-primary" :options="opts" >{{ realButtonTitle }}</BookingButton>
        </div>
    </div>
</template>
<script>
import Front from './Front'
import BookingFormDemo from './Components/BookingFormDemo'
export default {
    extends: Front,
    components:{
      BookingFormDemo: window.wappointmentExtends.filter('BookingDemoComponent', BookingFormDemo)
    },
    methods: {

        toggleBookForm() {
            if(this.disabledButtons) {
                let nextStepIndex = this.options.editionsSteps.findIndex((element) => element.key == 'button') + 1
              this.options.eventsBus.emits('stepChanged', this.options.editionsSteps[nextStepIndex].key)
              return
            } 
        },
    }
}
</script>
