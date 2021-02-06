<template>
    <div  class="d-flex">
        <div v-for="connectionKey in allConnections" class="ml-2 slot" :class="{disabled: !isConnected(connectionKey)}"  
            :data-tt="connectionDescription(connectionKey)"  >
            <img :src="connectionImage(connectionKey)" />
        </div>
    </div>
</template>

<script>
export default {
    props: {
        connections:{
            type:Array,
        },
        allConnections:{
            type:Array,
            default: () => ['zoom','google', 'googlemeet']
        }
    },

    computed: {
        resourcesUrl(){
            return window.apiWappointment.resourcesUrl+'images/'
        },
        servicesConnected(){
            let servicesC = []
            if(this.is_dotcom_connected){
                for (let i = 0; i < this.connections.length; i++) {
                    const service = this.connections[i]
                    servicesC.push(service)
                    if(service == 'google'){
                        servicesC.push('googlemeet')
                    }
                }
            }
            return servicesC
        }
    },
    methods:{
        isConnected(connectionKey){
            return this.connections.indexOf(connectionKey) !== -1
        },
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
<style >
.slot {
    border-radius: .3rem;
    font-size: 13px;
}
.slot img{
  max-height: 20px;
}
.slot.disabled img {
    filter:grayscale(1);
}
.slot.disabled:hover img {
    filter:grayscale(0);
}
.slot[data-tt]::before{
  min-width: 300px;
}
.slot[data-tt]::before,
.slot[data-tt]::after{
  bottom: 140%;
  left: 10px;
}
.wservices-list .slot {
    margin-bottom: .9em;
}
</style>