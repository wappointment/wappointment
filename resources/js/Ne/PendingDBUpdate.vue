<template>
    <div>
        <div class="wappo-db-update">
            <WPNotice>
                <p>Wappointment has improvements requiring a Database update: <button class="btn btn-primary btn-sm" @click="runMigrate">Run update</button></p>
            </WPNotice>
        </div>
        <WapModal v-if="show" :show="show" @hide="hidePopup">
            <h4 slot="title" class="modal-title">Update Required</h4>
            <div class="wappo-db-update" v-if="!updated">
                <h4>Wappointment has improvements requiring a Database update.</h4>
                <div >
                    <button class="btn btn-primary btn-lg btn-block" @click="runMigrate">Run update</button>
                </div>
            </div>
            <div v-else>
                <WLoader />
            </div>
        </WapModal>
    </div>
</template>
<script>
import abstractView from '../Views/Abstract'
import AppService from '../Services/V1/App'
import WPNotice from '../WP/Notice'
import Helpers from '../Modules/Helpers'
import CanPopAgain from '../Mixins/CanPopAgain'
export default {
    components: {WPNotice},
    extends: abstractView,
    mixins:[Helpers, CanPopAgain],
    data: () => ({
        serviceApp: null,
        updated: false
    }),
    created(){
        this.serviceApp = this.$vueService(new AppService)
    },
    methods: {
        runMigrate(){
            this.request(this.runMigrateRequest, {}, undefined, false, this.successUpdate)
        },
        async runMigrateRequest(){
          return await this.serviceApp.call('migrate') 
        },
        successUpdate(result){
            this.$WapModal().notifySuccess(result.data.message)
            this.updated = true
            this.$WapModal()
            .request(this.sleep(4000))
          window.location = window.apiWappointment.base_admin + '?page=wappointment_calendar'
        }

    }
}
</script>
<style>
.wappo-db-update .sub{
    font-size: .9rem;
}
</style>
