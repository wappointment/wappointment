<template>
    <div v-if="durationsOrdered.length>0">
      <div class="title wtitle" v-if="options!==undefined">{{options.service_duration.select_duration}}</div>
      <div class="d-flex flex-wrap " :class="getClass">
        <div class="wbtn wbtn-cell wbtn-duration wbtn-secondary d-flex align-items-center" role="button" v-for="(duration,idx) in durationsOrdered"  @click="selectDuration(duration)">
            <span class="mr-2 wduration" :class="{wsold: canSell(duration)}">{{duration.duration}}{{options.general.min}}</span>
            <span v-if="canSell(duration)" class="wprice">{{ getPrice(duration) }}</span>
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
            return window.wappointment_services === undefined || window.wappointment_services.is_admin === undefined ? 'wbtn wbtn-secondary wbtn-cell':'btn btn-secondary btn-cell'
        },
        getClass(){
            return {'justify-content-center': this.options !== undefined}
        },
        durationsOrdered(){
            return this.service.options.durations.sort((a,b) => a.duration > b.duration ?1:-1)
        },
        sellable(){
            return this.service.options.woo_sellable
        },
        currency(){
            return window.wappointment_woocommerce !== undefined ? window.wappointment_woocommerce.currency_symbol:''
        },
        
    },
    methods:{
        canSell(duration){
            return this.sellable && this.currency !== '' && [undefined, ''].indexOf(duration.woo_price) === -1
        },
        getPrice(duration){
            return duration.woo_price+this.currency
        },
        selectDuration(duration){ 
            if(this.disabledButtons && this.options !== undefined) {
              this.options.eventsBus.emits('stepChanged', 'service_location')
              return
            } 
            let data =  {duration:parseInt(duration.duration)}
            let nextScreen = 'BookingLocationSelection'
            if(this.service.locations.length == 1){
                data.location = this.service.locations[0]
                nextScreen = 'BookingCalendar'
            }
            this.$emit('durationSelected', nextScreen , data)
        }
    }
}   
</script>
<style>
.wbtn-duration .wduration{
    font-weight: normal;
}
.wbtn-duration .wduration.wsold::after{
    content: ' - ';
}
</style>