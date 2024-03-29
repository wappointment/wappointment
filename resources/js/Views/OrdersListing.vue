<template>
    <div>
        <div class="table-hover">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{get_i18n('client', 'orders') }}</th>
                        <th scope="col">{{get_i18n('total', 'orders') }}</th>
                        <th scope="col">{{get_i18n('order', 'orders') }}</th>
                        <th scope="col">{{get_i18n('paid_at', 'orders') }}</th>
                        <th scope="col">{{get_i18n('created_at', 'common') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="row-click" v-if="elements.length > 0" v-for="(order, idx) in elements">
                        <td>
                            <div>{{ idx + 1 }} <span>(id :{{ order.id}})</span> </div> 
                        </td>
                        <td>
                            <div class="d-flex align-items-center" v-if="getClient(order) !== false">
                                <div v-if="wIsEmpty(getClient(order, 'avatar'))">
                                    <img :src="getClient(order, 'avatar')" :alt="getClient(order, 'name')" class="border border-secondary wrounded mr-2">
                                </div>
                                <div>
                                    <div>{{ getClient(order, 'name') }}</div>
                                    <div>{{ getClient(order, 'email') }}</div>
                                </div>
                            </div>
                            <div v-else>
                                Client data missing
                            </div>
                        </td>
                         <td>
                            <div>{{ formatPrice(order.charge, true) }} <span class="text-muted small ml-2" v-if="order.tax_amount>0"> ({{get_i18n('tax', 'orders') }} {{ formatPrice(order.tax_amount, true) }})</span></div>
                            <div :class="getStatusClass(order.status)" >
                                {{ order.status_label }}
                            </div>
                            <a v-if="order.status==2" href="javascript:;" class="btn btn-secondary btn-sm" @click="refund(order)">{{get_i18n('refund', 'orders') }}</a>
                            <span v-if="order.status==1">
                                <a href="javascript:;" class="btn btn-secondary btn-sm" @click="markAsPaid(order)">{{get_i18n('markpaid', 'orders') }}</a>
                                <a href="javascript:;" class="btn btn-secondary btn-sm" @click="cancelOrder(order)">{{get_i18n('cancel', 'orders') }}</a>
                            </span>
                            <div class="text-muted small">{{ order.payment_label}}</div>
                        </td>
                        <td>
                            <div v-for="charge in order.prices">
                                {{ formatPrice(charge.price_value, true) }} - {{ charge.item_name }} - {{ displayModality(order.appointments, charge.appointment_id)}}
                            </div>
                        </td>
                        <td>
                            {{ order.paid_at }}
                        </td>
                        <td>
                            {{ order.created_at }}
                        </td>
                       
                    </tr>
                    <tr v-else>
                        {{get_i18n('empty_listing', 'common')}}
                    </tr>
                </tbody>
            </table>
        </div>
        <Pagination v-if="isPaginated" :pagination="pagination" @changePage="changePage"/>
    </div>
</template>

<script>

import OrdersService from '../Services/V1/Order'
import AbstractListing from './AbstractListing'
import CanFormatPrice from '../Mixins/CanFormatPrice'
export default {
    extends: AbstractListing,
    mixins: [CanFormatPrice],
    created(){
        this.mainService = this.$vueService(new OrdersService)
    },
    data: () => ({
        keyDataSource:'orders'
    }),
    methods: {

        refund(order){
            this.$WapModal().confirm({
                title: 'Do you really want to refund this order?',
            }).then((result) => {
                if(result === true){
                    this.request(this.refundOrderRequest,order,undefined,false,this.actionConfirmed)
                }
            })
            
        },
        getClient(order, attribute){
            let client = this.getStoredClientObject(order)
            return !this.wIsEmpty(client) ? client[attribute]:false
        },
        getStoredClientObject(order){
            return !this.wIsEmpty(order.options) && !this.wIsEmpty(order.options.client) ? order.options.client : this.getClientObject(order) 
        },
        getClientObject(order){
            return !this.wIsEmpty(order.client)? order.client:false
        },
        async refundOrderRequest(order){
            return await this.mainService.call('refund',{order_id:order.id})
        },
        actionConfirmed(response){
            this.serviceSuccess(response)
            this.reload()
        },

        cancelOrder(order){
            this.$WapModal().confirm({
                title: 'Do you really want to cancel this order?',
            }).then((result) => {
                if(result === true){
                    this.request(this.cancelOrderRequest,order,undefined,false,this.actionConfirmed)
                }
            })
            
        },
        async cancelOrderRequest(order){
            return await this.mainService.call('cancel',{order_id:order.id})
        },

        markAsPaid(order){
            this.$WapModal().confirm({
                title: 'Do you really want to mark this order as paid?',
            }).then((result) => {
                if(result === true){
                    this.request(this.markAsPaidRequest,order,undefined,false,this.actionConfirmed)
                }
            })
            
        },
        async markAsPaidRequest(order){
            return await this.mainService.call('markpaid',{order_id:order.id})
        },

        afterLoaded(response){
            this.$emit('loaded', response)
        },
        displayModality(appointments, id){
            if(appointments.length == 0){
                return ''
            }
            let appointmentData = appointments.find(e => e.id == id)
            return appointmentData.location_label
        },
        getStatusClass(status){
            switch (status) {
                case 0:
                    return 'text-primary'
                case 1:
                    return 'text-danger'
                case 2:
                    return 'text-success'
                case -1:
                    return 'text-muted'
                case -2:
                    return 'text-warning'
            }
        }

    }
}   

</script>
