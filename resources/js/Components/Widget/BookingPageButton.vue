<template>
    <div class="card d-flex flex-row justify-content-between" >
        <div class="h5 my-1 d-flex align-items-center">
            <span class="dashicons mr-1" :class="[hasBookingPage ? 'dashicons-text-page text-success': 'dashicons-dismiss text-danger']"></span>
            <span>{{ title }}</span>
        </div>
        <div v-if="hasBookingPage" >
            <span class="mr-2" :class="{'text-danger':viewData.booking_page_id === 0}">You don't have a booking page yet</span> 
            <button class="btn btn-secondary" @click="showCreateBookingPage = true">Create one</button>
        </div>
        <div v-else >
            <a :href="viewData.booking_page_url" target="_blank" class="btn btn-link btn-xs" >View</a> |
            <a :href="'post.php?post='+viewData.booking_page_id+'&action=edit'" target="_blank" class="btn btn-link btn-xs" >Edit</a>
        </div>
        <WapModal v-if="showCreateBookingPage" large :show="showCreateBookingPage" @hide="showCreateBookingPage = false">
            <h4 slot="title" class="modal-title">Create a booking page</h4>
            <CreateBookingPage :widgetDefault="viewData.widget" :save="true" @saved="savedPage"/>
        </WapModal >
    </div>
</template>

<script>
import CreateBookingPage from './CreateBookingPage' 
export default {
    props: {
        viewData:{
            type: Object
        },
        title:{
            type: String,
            default: 'Booking Page'
        },
    },
    data() {
        return {
            showCreateBookingPage: false,
        }
    },
    components: {
        CreateBookingPage
    },
    computed: {
        hasBookingPage(){
            return this.viewData.booking_page_id !== 0 && this.viewData.booking_page_url !== false
        }
    },
    methods:{
        savedPage(page_id){
            this.showCreateBookingPage = false
            this.$emit('saved',page_id)
        }
    }
}
</script>