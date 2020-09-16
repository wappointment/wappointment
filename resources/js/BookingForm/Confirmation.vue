<template>
    <div class="confirmation-cell">
        <div class="success d-flex align-items-center">
            <div class="bigicon"><WapImage :faIcon="'calendar-check'" size="md" /> </div>
            <div class="text-conf">{{options.confirmation.confirmation}}</div>
        </div>
        <div class="confirmation-summary">
            <div>
                <span class="wlabel">{{options.confirmation.when}}</span>
                <span>{{ appointment_starts_at }}</span>
            </div>
            <div>
                <span class="wlabel">{{options.confirmation.service}}</span>
                <span>{{ service.name }}</span>
            </div>
            <div>
                <span class="wlabel">{{options.confirmation.duration}}</span>
                <span>{{service.duration}}{{getMinText}}</span>
            </div>
            
        </div>
        <div class="wdescription" v-if="isApprovalManual">{{options.confirmation.pending}}</div>
        <div v-if="physicalSelected">
            <div class="wdescription">{{options.confirmation.physical}} </div>
            <div class="address-service">
                <BookingAddress :service="service">
                    <WapImage :faIcon="'map-marked-alt'" size="lg" /> </BookingAddress>
            </div>
            
            <BookingAddress :iframe="true" :service="service">
                <WapImage :faIcon="'map-marked-alt'" size="lg" /> 
            </BookingAddress>
        </div>
        <div class="wdescription" v-if="phoneSelected">
            {{options.confirmation.phone}} <strong>{{ getClientPhone}}</strong>
        </div>
        <div class="wdescription" v-if="skypeSelected">
            {{options.confirmation.skype}} <strong>{{ getClientSkype }}</strong> 
        </div>
        <div class="wdescription my-2 text-center">
            <transition name="slide-fade">
                <SaveButtons v-if="showSaveButtons" :service="service" :showResult="showResult" :appointment="appointment"
                :staff="staff" :currentTz="timeprops.currentTz" :physicalSelected="physicalSelected"></SaveButtons>
                <span v-else class="wbtn-primary-light wbtn d-flex align-items-center d-flex-inline" @click="showSaveButtons=true">
                    <WapImage :faIcon="'calendar-alt'" size="md" /> <span class="ml-2">{{options.confirmation.savetocal}}</span>
                </span>
            </transition>
        </div>
    </div>
</template>

<script>
import BookingAddress from './Address'
import SaveButtons from './SaveButtons'
import minText from './minText'
export default {
    components: {
        BookingAddress,
        SaveButtons,
    }, 
    mixins: [minText],
    props: [
        'appointment',
        'service', 
        'result', 
        'isApprovalManual', 
        'timeprops',
        'staff',
        'options',
        'appointment_starts_at',
    ],
    data: () => ({
        showSaveButtons: false,
        selectedServiceType: '',
        showResult: null,
    }),

    created(){
        this.showResult = this.result
        this.selectedServiceType = this.showResult.type

        if(this.options.demoData !== undefined){
            this.options.eventsBus.listens('dataDemoChanged', this.dataChanged)
        }
    },
    computed: {
        getClientPhone(){
            if(this.showResult.client !== undefined){
                return this.showResult.client.options.phone
            }
            return this.showResult.phone
        },
        getClientSkype(){
            if(this.showResult.client !== undefined){
                return this.showResult.client.options.skype
            }
            return this.showResult.skype
        },
        phoneSelected(){
            return this.selectedServiceType == 'phone'
        },
        physicalSelected(){
            return this.selectedServiceType == 'physical'
        },
        skypeSelected(){
            return this.selectedServiceType == 'skype'
        },
    },
    methods: {
        dataChanged(dataNew){
            this.showResult = dataNew
            this.selectedServiceType = this.showResult.type
        }
    }
}
</script>
<style>
.bigicon{
    font-size: 3em;
    margin-right: .2em;
}
.success .text-conf{
    text-align: left;
    font-size: 1.6em;
    line-height: 1.2em;
}
.align-items-center {
    -webkit-box-align: center !important;
    -ms-flex-align: center !important;
    align-items: center !important;
}
.mb-2, .my-2 {
    margin-bottom: .5em !important;
}
.mt-2, .my-2 {
    margin-top: .5em !important;
}

.confirmation-summary{
    padding: 2em 2em 1.5em 2em;
    font-size: .8em;
}
.confirmation-summary > div{
    margin-bottom:.5em;
}
.confirmation-summary .wlabel{
    font-weight: bold;
}
.wdescription{
    font-size: .8em;
}

</style>
