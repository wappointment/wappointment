<template>
    <div class="wrapper-dotcom p-4">
        <div class="d-flex justify-content-center align-items-center flex-wrap flex-xl-nowrap">
            <div >
                <h3 class="d-flex align-items-center">
                    <div><img :src="calendar.avatar" class="img-fluid wrounded" :alt="calendar.name" width="40" /></div>
                    <div class="ml-2">
                        <div>{{ calendar.name }}</div>
                    </div>
                </h3>
                <div v-if="calendar.connected">
                    <div>Connected to wappointment.com with: </div>
                    <div><strong>{{calendar.connected.account_key }}</strong></div>
                    <a class="small" href="javascript:;" @click="refresh" data-tt="Refresh service status">refresh</a> - <a data-tt="Disconnect from Wappointment.com" class="small text-danger" href="javascript:;" @click="disconnectWappo">disconnect</a>
                    <Connections :connections="calendar.connected.services" :vertical="true" :showLabel="true" />
                    <div class="small"><a :href="addMoreService" target="_blank" data-tt="Enable new services from your account page on Wappointment.com">Enable/Disable services</a></div>
                </div>
                <div v-else>
                    <div class="mb-3">
                    <InputPh v-model="account_key" :ph="get_i18n('cals_account_enter_code', 'settings')" /> 
                    </div>
                    <button class="btn btn-primary btn-block btn-lg mb-2" @click="connectToWappo">{{ get_i18n('cals_account_connect', 'settings') }}</button>
                    <div class="text-muted">{{ get_i18n('cals_account_donthave', 'settings') }} <a :href="createAccount" target="_blank">{{ get_i18n('cals_account_create_free', 'settings') }}</a></div>
                </div>
            </div>

            <div v-if="!calendar.connected" class="create-account ml-xl-4">
                <h2>{{ get_i18n('cals_account_create_title', 'settings') }}</h2>
                <ul>
                    <li>{{ get_i18n('cals_account_connect_fav', 'settings') }}</li>
                    <li>
                    <ol>
                        <li>{{ get_i18n('cals_account_connect_fav_1', 'settings') }} <strong ><img :src="connectionImage('zoom')" /> Zoom</strong> & <strong ><img :src="connectionImage('googlemeet')" /> Google Meet</strong></li>
                        <li>{{ get_i18n('cals_account_connect_fav_2', 'settings') }} <strong ><img :src="connectionImage('google')" /> Google Calendar</strong></li>
                        <li>{{ get_i18n('cals_account_connect_fav_3', 'settings') }}</li>
                    </ol>
                    </li>
                </ul>
            </div>
        </div>
        
    </div>
</template>

<script>

import Connections from '../RegularAvailability/Connections'
import ConnectionsMixins from '../RegularAvailability/ConnectionsMixins'
import abstractView from '../Views/Abstract'
export default {
    extends: abstractView,
    components: { Connections },
    mixins: [ConnectionsMixins],
    props: {
        calendar:{
            type: Object,
        },
    },
    data() {
        return {
            account_key: '',
        }
    },

    computed:{
        createAccount(){
            return window.apiWappointment.apiSite + '/register?site='+encodeURIComponent(apiWappointment.root)+'&version='+apiWappointment.version
        },
        addMoreService(){
            return window.apiWappointment.apiSite + '/client/account'
        },
    },
    methods: {
        refresh() {
            let data_save = {account_key: this.account_key}
            if(this.calendar.wp_uid !== undefined){
                data_save.id = this.calendar.id
            }
            this.request(this.refreshWappoRequest,data_save,null, false,this.successRefreshed)
        },

        async refreshWappoRequest(data_save) {
            return await this.service.call('refreshdotcom', data_save)
        },
        successRefreshed(response){
            this.is_dotcom_connected = response.data.data.dotcom
            this.$WapModal().notifySuccess(response.data.message, 1)
            this.$emit('reload')
        },
        successConnected(response){
            this.is_dotcom_connected = response.data.data.dotcom
            this.$WapModal().notifySuccess(response.data.message, 1)
            this.$emit('reload')
            //this.$WapModal().reload()
        },

        connectToWappo() {
            let data_save = {account_key: this.account_key}
            if(this.calendar.wp_uid !== undefined){
                data_save.id = this.calendar.id
            }
            this.request(this.connectToWappoRequest,data_save,null, false,this.successConnected)
        },

        async connectToWappoRequest(params) {
            return await this.service.call('connectdotcom', params)
        },

        disconnectWappo(){
            let data_save = {}
            if(this.calendar.wp_uid !== undefined){
                data_save.id = this.calendar.id
            }
            this.request(this.disconnectToWappoRequest,data_save,null,false, this.successDisconnected)
        },

        async disconnectToWappoRequest(params) {
            return await this.service.call('disconnectdotcom', params)
        },
        
        successDisconnected(response){
            this.is_dotcom_connected = false
            this.$WapModal().notifySuccess(response.data.message,1)
            this.$emit('reload')
        },

    }
}   
</script>
<style>
.wrapper-dotcom{
  border-radius: .4rem;
  max-width: 850px;
  margin: 0 auto;
}
.create-account {
	background: #d6d5ff;
	padding: .8em;
	border-radius: .4em;
	min-width: 380px;
  max-width: 480px;
}
</style>