

<template>
    <div>
        <NoticeButton :config="nb" @click="showConfiguration" v-if="!status"></NoticeButton>
        <div v-else>
            <div class="d-flex align-items-center">
                <span class="dashicons dashicons-yes text-success mail_configured"></span>
                <span>Email sending method configured</span>
                <button class="ml-2 btn btn-sm btn-secondary" @click="showConfiguration">Reconfigure it</button>
            </div>
            <div class="small text-muted ml-2">
                <span class="text-dark">Please make sure you receive emails.</span> 
                <span>If you have deliverability issues, reconfigure it with Mailgun, it is free and it just works.</span>
            </div>
        </div>
    </div>
</template>

<script>

import NoticeButton from '../Components/NoticeButton'
export default {
    components: {NoticeButton},
    props:{
        status: {
            type: Boolean,
            default: false
        }
    },
    data: () => ({
        service: null,
        nb: {
            title: 'No emails will be sent without configuring the sending method first',
            button: 'Configure it now',
            icon: 'dashicons dashicons-email mr-2'
        },
    }),

    methods: {
        showConfiguration(){
            this.$emit('configureEmail')
        },       
    }  
}
</script>

