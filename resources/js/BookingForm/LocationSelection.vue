<template>
    <div v-if="locations.length>0">
      <div class="title wtitle" v-if="options!==undefined">{{options.service_location.select_location}}</div>
      <div class="d-flex flex-wrap">
        <div v-for="(location,idx) in locations" class="d-flex align-items-center wbtn wbtn-cell wbtn-service wbtn-secondary" @click="selectLocation(location)">
            <WapImage class="mx-2" :element="location" :desc="location.name" size="md" />
            <div class="service-label">
                <div>{{ location.name }}</div>
            </div>
        </div>
      </div>
    </div>
</template>

<script>
export default {
    props:['service','relations','options'],
    data: () => ({
        disabledButtons: false,
    }),
    created(){
        if(this.options !== undefined && this.options.demoData !== undefined){
            this.disabledButtons = true
        }
    },
    computed:{
        locations(){
            return this.service.locations
        },
    },
    methods:{
        selectLocation(location){ 
            if(this.disabledButtons  && this.options !== undefined) {
              this.options.eventsBus.emits('stepChanged', 'selection')
              return
            } 
            this.$emit('locationSelected', 'BookingCalendar', {location:location})
        }
    }
}   
</script>
