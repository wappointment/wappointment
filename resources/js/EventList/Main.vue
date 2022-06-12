<template>
    <div class="wap-bf show has-scroll">
      <div class="wap-form-body">
        <Loader v-if="loading"/>
        <div v-if="isLoaded" class="p-2">
          <div v-if="!service" class="werror">
            {{ options.general.noappointments }}
          </div>
          <template v-else>
            <h2>{{ service.name }}</h2>
            <Event v-for="event in eventFromList" :key="event[4]" 
            :event="event" :buffer="bufferInSec" :tz="tz" :options="options" 
            @selectEvent="selectedEvent"/>
          </template>
        </div>
      </div>
    </div>
</template>
<script>
import AbstractFront from '../BookingForm/AbstractFront'
import Event from './Event'
import Dates from '../Modules/Dates'
export default {
    extends: AbstractFront,
    components: {Event},
    mixins: [Dates],
    props: ['list', 'options'],
    data() {
      return {
        tz: '',
        selectedStaff: false,
      }
    },
    mounted(){
      this.tz = this.tzGuess()
      this.loadAvailabiity()
    },
    computed:{
      isLoaded(){
        return this.viewData !== null
      },
      allAvailabilityMerged(){
        if(!this.isLoaded) return []
        return this.viewData.staffs.map(staff => staff.availability.filter(this.filterEvents.bind(null,staff)))
      },
      
      eventFromList(){
        let concated = []
        for (const iterator of this.allAvailabilityMerged) {
          concated = concated.concat(iterator)
        }
        return concated
      },
      firstEvent(){
        return this.isLoaded && this.eventFromList.length > 0? this.eventFromList[0]:[]
      },
      service(){
        if(this.firstEvent.length === 0){
          return false;
        }
        let service_id = parseInt(this.firstEvent[3])
        return this.viewData.services.find(e => parseInt(e.id) === service_id)
      },
      bufferInSec(){
        return this.viewData.buffer_time * 60
      }
    },
    methods: {
      filterEvents(staff,e){
        if(e.length > 5 && e[5] == this.list){
          this.selectedStaff = staff
          return true
        }
      },
      selectedEvent(event){
        event.staff = Object.assign({},this.selectedStaff)
        this.$emit('selectedEvent', event)
      },
      formatDate(unixts){
        return momenttz.unix(unixts).tz(this.currentTz)
      },
      loadAvailabiity(){
          this.loading = true
          this.initValueRequest()
          .then(this.loaded)
          .catch(this.serviceError)
      },
    }
}
</script>
