<template>
    <div v-if="locations.length>0">
      <div class="title wtitle" v-if="options!==undefined">{{options.service_location.select_location}}</div>
      <div class="d-flex flex-wrap">
        <div v-for="(location,idx) in locations" class="d-flex align-items-center wbtn wbtn-cell wbtn-service wbtn-secondary" @click="selectLocation(location)">
            <WapImage class="mx-2" :element="location" :desc="location.name" size="md" />
            <div class="service-label">
                <div>{{ location.name }}</div>
                <div class="description" v-if="hasDesc(location)">{{ hasDesc(location) }}</div>
            </div>
        </div>
      </div>
    </div>
</template>

<script>
import IsDemo from '../Mixins/IsDemo'
export default {
    mixins:[IsDemo],
    props:['service','relations','options'],
    computed:{
        locations(){
            return this.service.locations
        },
    },
    methods:{
        selectLocation(location){ 
            if(this.triggersDemoEvent('selection')){
                return
            }
            this.$emit('locationSelected', 'BookingCalendar', {location:location})
        },
        hasDesc(location){
            return location.options !== undefined && 
            location.options.description_on !== undefined 
            && location.options.description_on ?location.options.description:false
        }
    }
}   
</script>
