<template>
    <FullCalendar  v-bind="configPrepared" v-on="eventsPrepared" ref="calendarcore"/>
</template>

<script>
    import FullCalendar from '@fullcalendar/vue'
    import dayGridPlugin from '@fullcalendar/daygrid'
    import timeGridPlugin from '@fullcalendar/timegrid'
    import interactionPlugin from '@fullcalendar/interaction'
    import momentTimezonePlugin from '@fullcalendar/moment-timezone' //tz recognition
    import momentPlugin from '@fullcalendar/moment' //formatting
    const jQuery = window.jQuery

    //modify the css function in order to implement a change on fc-now
    var overridenCss = jQuery.fn.css;
    jQuery.fn.css = function (name,value){
        let result =  overridenCss.apply(this, arguments); 
        if(jQuery(this).hasClass('fc-now-indicator-line') && name == 'top' && value !== '' && value!==undefined && value > 0){
            jQuery(this).css('height',value)
            jQuery(this).css('top',0)
        }
        return result
    }

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
                calendarAPI: undefined
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
            const cal = this.calendar
            this.isReady = true
            this.$emit('isReady')
        },

        methods: {
            apiReady(){
                return this.calendarAPI !== undefined
            },

            unselect() {
                return jQuery(this.$el).fullCalendar('unselect')
            },

            fireMethod(...options) {
                if(!this.apiReady()) {
                    console.log('API not ready for call fireMethod', ...options)
                }

                if(options.indexOf('next') !== -1) return this.getApi.next()
                if(options.indexOf('prev') !== -1) return this.getApi.prev()
                if(options.indexOf('getDate') !== -1) return this.getApi.getDate()
                if(options.indexOf('refetchEvents') !== -1) return this.getApi.refetchEvents()

            }, 

             option(optioname, optionvalue) {
                 if(!this.apiReady()) {
                    console.log('API not ready for call option', optioname, optionvalue)
                }

                return this.getApi.setOption(optioname, optionvalue)
            }, 
        },



    }
</script>
