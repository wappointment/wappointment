<template>
    <div :class="getClasses" @click="selectService">
        <WapImage v-if="serviceHasIcon" :element="service" :desc="service.name" size="lg" />
        <div class="service-label" >
            <div class="service-name" >{{ service.name }}</div>
            <div class="description" v-if="hasDesc">{{ service.options.description }}</div>
            <div v-if="sellable && !priceAlignRight" class="service-price" >{{ getPriceRange }}<span class="price-currency">{{ currency }}</span></div>
            <slot />
        </div>
        <div v-if="sellable && priceAlignRight" class="service-price price-right" :class="{'als':hasDesc}" >{{ getPriceRange }}<span class="price-currency">{{ currency}}</span></div>
    </div>
    
</template>

<script>
import HasWooVariables from '../Mixins/HasWooVariables'
import PriceFormatMixin from './PriceFormatMixin'
export default {
    props:['service','options', 'selected', 'viewData', 'extraClass'],
    name: 'ServiceButton',
    mixins: [HasWooVariables, PriceFormatMixin],
    computed: {
        priceAlignRight(){
            return [undefined, false].indexOf(this.options.service_selection.check_price_right) === -1
        },
        getClasses(){
            let classses = 'wbtn wbtn-cell wbtn-secondary wbtn-service d-flex align-items-center'

            if(this.extraClass!= ''){
                classses += ' ' + this.extraClass
            }
            if(this.selected === true){
                classses += ' selected'
            }
            return classses
        },
        currency(){
            return this.currencySymb
        },
        serviceHasIcon(){
            return this.service.options.icon != ''
        },
        sellable(){
            return this.service.options.woo_sellable  && this.hasPrice && this.currency != ''
        },
        hasPrice(){
            return this.getPrices.length > 0
        },
        getPrices(){
            let prices = []
            for (let i = 0; i < this.service.options.durations.length; i++) {
                const element = this.service.options.durations[i]
                if([undefined,''].indexOf(element.woo_price) === -1){
                    prices.push(element.woo_price)
                }
            }

            return prices
        },
        hasDesc(){
            return ['',undefined].indexOf(this.service.options.description) === -1
        },
        getPriceRange(){
            let prices = this.getPrices
            if(prices.length > 1){
                return this.formatPrice(Math.min.apply(null, prices))+ ' - ' + this.formatPrice(Math.max.apply(null, prices))
            }

            return this.formatPrice(prices[0])
        },
    },
    methods:{

        selectService(){
            this.$emit('selectService', this.service)
        }
    }
}   
</script>
<style>

.wap-front .wbtn.wbtn-cell.wbtn-secondary.wbtn-service,
.wap-front .wbtn.wbtn-cell.wbtn-secondary.wbtn-service:not(:disabled):not(.disabled):active,
.wap-front .wbtn.wbtn-cell.wbtn-secondary.wbtn-service:not(:disabled):not(.disabled).active{
    width: 100%;
    margin: .4em;
    border: none;
    overflow: hidden;
    padding: .4em;
    max-width: 340px;
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