<template>
    <div>
        <div v-if="canSubscribe" class="mb-3">
            <slot></slot>
            <div class="input-group">
                <input type="email" class="form-control" :class="{'is-invalid':hasEmailError}" :placeholder="placeholder" v-model="email" :aria-label="placeholder" >
                <div class="input-group-append">
                    <button class="btn btn-primary" @click="subscribe"><span class="dashicons dashicons-email"></span> {{ buttonLabel }}</button>
                </div>
                <div class="invalid-feedback" v-if="hasEmailError">
                  <p class="m-0" v-for="errorMsg in getFieldErrors('email')"> {{ errorMsg}}</p>
                </div>
            </div>
        </div>
        <div v-else class="small">
             <p class="d-flex align-items-center m-0"><span class="dashicons dashicons-yes text-success"></span> <span>Subscribed ({{ defaultEmail }})</span></p>
             <button class="btn btn-link btn-sm" @click="subscribeAgain">{{ subscribeOtherEmail }}</button>
        </div>
    </div>
</template>

<script>
import WappointmentService from '../Services/V1/Wappointment'
import abstractView from '../Views/Abstract'
export default {
    extends: abstractView,
    props: {
        list: {
            type: String,
        },
        defaultEmail: {
            type: String,
        },
        buttonLabel: {
            type: String,
            default: 'Subscribe'
        },
        placeholder: {
            type: String,
            default: 'Enter an email'
        },
        statuses: {
            type: Array
        },
        subscribeOtherEmail: {
            type: String,
            default: 'Use another email'
        }
    },
    data: () => ({
        serviceWappointment: null,
        email: '',
        changeEmail: false,
        serverValidationErrors: undefined
    }),
    created(){
        this.serviceWappointment = this.$vueService(new WappointmentService)
        this.email = this.defaultEmail
    },
    computed: {
        hasEmailError(){
            return this.getFieldErrors('email')
        },
        isValid(){
            return this.email != ''
        },
        canSubscribe(){
            return !this.isSubscribed || this.changeEmail === true
        },
        isSubscribed() {
            let emailStatus = this.defaultEmailList
            return emailStatus!== false && emailStatus['lists'][this.list] !== undefined
        },
        defaultEmailList(){
            let defaultEmail = this.defaultEmail
            let result = this.statuses.filter((e) => e.email == defaultEmail)
            return result.length == 1 ? result[0]: false;
        }
    },
    methods: {
        getFieldErrors(field){
            return this.serverValidationErrors !== undefined && this.serverValidationErrors[field] !== undefined ? this.serverValidationErrors[field]:false
        },
        subscribeAgain(){
            this.changeEmail = true
            this.email = ''
        },
       successSubscribed(response){
          this.$WapModal().notifySuccess(response.data.message)
          if(response.data.result !== undefined && response.data.result.statuses !== undefined){
            this.$emit('updatedStatuses', response.data.result.statuses)
          }
        },
        failedRequestWrap(error){
            this.failedRequest(error)
            if(error.response !== undefined){
                console.log('failedRequest', error.response )
              if(error.response.data.data.errors.validations !== undefined) this.serverValidationErrors = error.response.data.data.errors.validations
            }
        },
        subscribe(){
          this.$WapModal().request(this.subscribeWappointmentRequest()).then(this.successSubscribed).catch(this.failedRequestWrap)
        },
        async subscribeWappointmentRequest(addon) {
            return await this.serviceWappointment.call('subscribe', { list: this.list, email:this.email })
        },
    },

}
</script>