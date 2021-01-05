<template>
    <div v-if="locations.length>0">
      <div class="title wtitle" v-if="options!==undefined">{{options.service_location.select_location}}</div>
      <div class="d-flex flex-wrap" :class="getClass">
          <div v-for="(location,idx) in locations" :class="getClasses" @click="selectLocation(location)">
            <WapImage :element="location" :desc="location.name" size="md" /> {{ location.name }}
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
        getClasses(){
            return window.wappointment_services === undefined || 
            (window.wappointment_services.is_admin === undefined ) || (this.options !== undefined && this.options.demoData !== undefined) ? 'wbtn wbtn-cell wbtn-secondary':'btn btn-cell btn-secondary'
        },
        locations(){
            return this.service.locations
        },
        getClass(){
            return {'justify-content-center': this.options !== undefined}
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
