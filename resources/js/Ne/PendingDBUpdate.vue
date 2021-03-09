<template>
    <WapModal v-if="showing" :show="showing" @hide="hide">
        <h4 slot="title" class="modal-title">Update Required</h4>
        <div class="wappo-db-update" v-if="!updated">
            <p>Wappointment has improvements requiring a Database update.</p>
            <div >
                <button class="btn btn-primary btn-lg btn-block" @click="runMigrate">Run update</button>
            </div>
        </div>
        <div v-else>
            <WLoader />
        </div>
    </WapModal>
  
</template>
<script>
import abstractView from '../Views/Abstract'
import AppService from '../Services/V1/App'
import WPNotice from '../WP/Notice'
import Helpers from '../Modules/Helpers'
export default {
    components: {WPNotice},
    extends: abstractView,
    mixins:[Helpers],
    data: () => ({
        serviceApp: null,
        showing:true,
        updated: false
    }),
    created(){
        this.serviceApp = this.$vueService(new AppService)
    },
    methods: {
        hide(){
            this.showing = false
        },
        runMigrate(){
            this.request(this.runMigrateRequest, {}, undefined, false, this.successUpdate)
        },
        async runMigrateRequest(){
          return await this.serviceApp.call('migrate') 
        },
        successUpdate(result){
            this.$WapModal().notifySuccess(result.data.message)
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
