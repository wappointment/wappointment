<template>
    <FullCalendar v-bind="configPrepared" v-on="eventsPrepared" ref="calendarcore"/>
</template>

<script>
    import FullCalendar from '@fullcalendar/vue'
    import dayGridPlugin from '@fullcalendar/daygrid'
    import timeGridPlugin from '@fullcalendar/timegrid'
    import interactionPlugin from '@fullcalendar/interaction'
    import momentTimezonePlugin from '../Plugins/fcmoment-timezone/main.esm' //tz recognition
    import momentPlugin from '../Plugins/fcmoment/main.esm'
    
    export default {
        components:{
            FullCalendar
        },
        data() {
            return {
                calendarPlugins: [ dayGridPlugin, timeGridPlugin, interactionPlugin, momentTimezonePlugin, momentPlugin],
                configPrepared: {},
                eventsPrepared: {},
                isReady: false,
                calendarAPI: undefined,
            }
        },

        props: {

            config: {
                type: Object,
                default() {
                    return {}
                },
            },
        },

        computed: {
            getApi(){
                return this.calendarAPI
            },
        },
        
        created(){
            this.configPrepared = this.config.props
            this.configPrepared.plugins = this.calendarPlugins
            this.eventsPrepared = this.config.events
        },

        mounted() {
            this.calendarAPI = this.$refs.calendarcore.getApi()
            this.isReady = true
            this.$emit('isReady')
        },

        methods: {
            
            apiReady(){
                return this.calendarAPI !== undefined
            },
            next(lastDay){
                let nextweek = lastDay.clone().add(1,'day')
                this.getApi.gotoDate(nextweek.format())
                this.getApi.refetchEvents()
                return nextweek
            },
            prev(firstDay){
                let prevweek = firstDay.clone().subtract(7,'days')
                this.getApi.gotoDate(prevweek.format())
                this.getApi.refetchEvents()
                return prevweek
            },
            refresh(){
                this.getApi.refetchEvents()
            },
            fireMethod(...options) {
                if(!this.apiReady()) {
                }
                if(options.indexOf('today') !== -1) return this.getApi.today()
                if(options.indexOf('next') !== -1) return this.getApi.gotodate()
                if(options.indexOf('prev') !== -1) return this.getApi.prev()
                if(options.indexOf('getDate') !== -1) return this.getApi.getDate()
                if(options.indexOf('refetchEvents') !== -1) return this.getApi.refetchEvents()
                if(options.indexOf('unselect') !== -1) return this.getApi.unselect()

            }, 

             option(optioname, optionvalue) {
                 if(!this.apiReady()) {
                }

                return this.getApi.setOption(optioname, optionvalue)
            }, 
        },



    }
</script>
