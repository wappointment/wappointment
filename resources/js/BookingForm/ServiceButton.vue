<template>
    <div :class="getClasses" @click="selectService(service)">
        <WapImage v-if="serviceHasIcon" :element="service" :desc="service.name" size="lg" />
        <div class="service-label" >
            <div class="service-name" >{{ service.name }}</div>
            <div class="description" v-if="hasDesc(service)">{{ service.options.description }}</div>
            <div v-if="sellable(service) && !priceAlignRight" class="service-price" >{{ getPriceRange(service) }}<span class="price-currency">{{ currency}}</span></div>
        </div>
        <div v-if="sellable(service) && priceAlignRight" class="service-price price-right" :class="{'als':hasDesc(service)}" >{{ getPriceRange(service) }}<span class="price-currency">{{ currency}}</span></div>
    </div>
    
</template>

<script>
export default {
    props:['service','options', 'selected', 'viewData'],
    name: 'ServiceButton',
    computed: {
        priceAlignRight(){
            return [undefined, false].indexOf(this.options.service_selection.check_price_right) === -1
        },
        getClasses(){
            let classses = window.wappointment_services === undefined || window.wappointment_services.is_admin === undefined ? 'wbtn wbtn-cell wbtn-secondary wbtn-service':'btn btn-cell btn-secondary btn-service'
            classses = 'wbtn wbtn-cell wbtn-secondary wbtn-service d-flex align-items-center'
 
            if(this.selected === true){
                classses += ' selected'
            }
            return classses
        },
        currency(){
            return window.wappointment_woocommerce !== undefined ? window.wappointment_woocommerce.currency_symbol:''
        },
        serviceHasIcon(){
            return this.service.options.icon != ''
        }
    },
    methods:{
        hasDesc(service){
            return ['',undefined].indexOf(service.options.description) === -1
        },
        sellable(service){
            return service.options.woo_sellable  && this.hasPrice(service) && this.currency != ''
        },

        getPrices(service){
            let prices = []
            for (let i = 0; i < service.options.durations.length; i++) {
                const element = service.options.durations[i]
                if([undefined,''].indexOf(element.woo_price) === -1){
                    prices.push(element.woo_price)
                }
            }

            return prices
        },
        hasPrice(service){
            return this.getPrices(service).length > 0
        },
        getPriceRange(service){
            let prices = this.getPrices(service)
            if(prices.length > 1){
                return Math.min.apply(null, prices)+ ' - ' + Math.max.apply(null, prices)
            }

            return prices[0]
        },
        selectService(service){
            this.$emit('selectService', service)
        }
    }
}   
</script>
<style>
/* .wap-front  .wbtn.wbtn-cell.wbtn-secondary.wbtn-service {
    width: 80px;
    margin: .4em;
    overflow: hidden;
    padding: 0;
    transition: all .3s ease-in-out;
} */
.wap-front .wbtn.wbtn-cell.wbtn-secondary.wbtn-service,
.wap-front .wbtn.wbtn-cell.wbtn-secondary.wbtn-service:not(:disabled):not(.disabled):active,
.wap-front .wbtn.wbtn-cell.wbtn-secondary.wbtn-service:not(:disabled):not(.disabled).active{
    width: 100%;
    margin: .4em;
    border: none;
    overflow: hidden;
    padding: .4em;
    max-width: 430px;
    font-size: 1em;
}

.wap-front .wbtn-service.d-flex .service-label .service-name
{
    font-size: 1.2em;
    line-height: 1.2em;
} 

.wap-front .wbtn-service.d-flex .service-label .description
{
    font-size: .8em;
} 

.wap-front .wbtn-service.d-flex .service-price{
    font-size: 1em;
} 



.wap-front .wbtn-service .wap-icon-image {
    transition: all .3s ease-in-out;
    overflow: hidden;
    height: 80px;
    width: 80px;
    background-position: center center;
    background-size: cover;
}

.wap-front .wbtn-service .service-label{
    margin: 0 .5em;
    overflow: hidden;
    white-space: initial;
    text-align: center;
}


/* pills */

.wap-front .wbtn-service.d-flex .service-label{
    text-align: left;
} 
.service-price{
    font-weight: bold;
}
.service-price .price-currency{
    font-size: .7em;
}
.service-price.price-right{
    margin-left: auto !important;
}

.wap-front .als{
    align-self: flex-start !important;
}

</style>