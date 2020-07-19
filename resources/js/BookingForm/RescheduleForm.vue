<template>
    <BookingForm serviceAction="rescheduling" :appointmentkey="appointmentkey" :rescheduleData="rescheduleData" :options="options"></BookingForm>
</template>

<script>
import LoadingComponent from '../Components/Loaders/Bar'
import ErrorComponent from '../Components/Error'
import abstractFront from '../Views/abstractFront'
export default {
     props: ['appointmentkey', 'options', 'rescheduleData'],
     components: {
        BookingForm: () => ({
            // The component to load (should be a Promise)
            component: import(/* webpackChunkName: "group-bookingform" */ './Main'),
            // A component to use while the async component is loading
            loading: LoadingComponent,
            // A component to use if the load fails
            error: ErrorComponent,
            // Delay before showing the loading component. Default: 200ms.
            delay: 100,
            // The error component will be displayed if a timeout is
            // provided and exceeded. Default: Infinity.
            timeout: 6000
        })
    }, 
    extends: abstractFront,
    data: () => ({
       
    }),
    
}
</script>

<style>

</style>