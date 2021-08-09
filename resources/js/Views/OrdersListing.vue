<template>
    <div>
        <div class="table-hover">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Client</th>
                        <th scope="col">Total</th>
                        <th scope="col">Order</th>
                        <th scope="col">Paid at</th>
                        <th scope="col">Created at</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="row-click" v-if="elements.length > 0" v-for="(order, idx) in elements">
                        <td>
                            <div>{{ idx + 1 }} </div> 
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div v-if="order.client.avatar !== ''">
                                    <img :src="order.client.avatar" :alt="order.client.name" class="border border-secondary wrounded mr-2">
                                </div>
                                <div>
                                    <div>{{ order.client.name }}</div>
                                    <div>{{ order.client.email }}</div>
                                </div>
                            </div>
                        </td>
                         <td>
                            <div>{{ formatPrice(order.total, true) }}</div>
                            <div :class="getStatusClass(order.status)" >{{ order.status_label }}</div>
                            <div class="text-muted small">{{ order.payment_label}}</div>
                        </td>
                        <td>
                            <div v-for="charge in order.prices">
                                {{ formatPrice(charge.price.price, true) }} - {{ charge.price.name }} - {{ displayAppointment(order.appointments, charge.appointment_id)}}
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
                        You don't have any orders yet
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
        afterLoaded(response){
            this.$emit('loaded', response)
        },
        displayAppointment(appointments, id){
            let appointmentData = appointments.find(e => e.id == id)
            return appointmentData.location_label + ' '+(appointmentData.duration_sec/60)+'min' +' '
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
