<script>

export default {
    computed:{
        resourcesUrl(){
            return window.apiWappointment.resourcesUrl+'images/'
        },
    },
    methods:{
       connectionImage(connectionKey){
            switch (connectionKey) {
                case 'zoom':
                return this.resourcesUrl+'zoom.png'
                case 'google':
                return this.resourcesUrl+'google-calendar.png'
                case 'googlemeet':
                return this.resourcesUrl+'google-meet.png'
            }
        },
        connectionDescription(connectionKey){
            switch (connectionKey) {
                case 'zoom':
                return 'Automatically creates Zoom meeting for your new Video Meetings and save the meeting link in Wappointment'
                case 'google':
                return 'Automatically save new appointments to a secondary calendar in your Google Calendar account'
                case 'googlemeet':
                return 'Automatically generates Google Meet meetings for your new Video Meetings and save the meeting link in Wappointment'
            }
        },

        connectionLabel(connectionKey){
            switch (connectionKey) {
                case 'zoom':
                return 'Zoom'
                case 'google':
                return 'Google Calendar'
                case 'googlemeet':
                return 'Google Meet'
            }
        },
        
    }
}
</script>
