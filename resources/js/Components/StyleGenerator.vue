<template>
    <v-style>
        :root {
            --wappo-pri-bg: {{ hx_rgb(opts.colors.primary.bg) }};
            --wappo-pri-tx: {{ hx_rgb(opts.colors.primary.text) }};
            --wappo-pri-tx-lt: {{ hx_rgb(opts.colors.primary.text,.8) }};
            --wappo-sec-bg: {{ hx_rgb(opts.colors.secondary.bg) }};
            --wappo-sec-tx: {{ hx_rgb(opts.colors.secondary.text) }};
            --wappo-sec-sel-bg: {{ hx_rgb(opts.colors.secondary.bg_selected) }};
            --wappo-sec-sel-tx: {{ hx_rgb(opts.colors.secondary.text_selected) }};
            --wappo-header-tx: {{ hx_rgb(opts.colors.header.text) }};
            --wappo-body-tx: {{ hx_rgb(opts.colors.body.text) }};
            --wappo-body-bg: {{ hx_rgb(opts.colors.body.bg) }};
            --wappo-success-tx: {{ hx_rgb(opts.colors.confirmation.text) }};
            --wappo-error-tx: {{ hx_rgb(opts.colors.form.error) }};
            --wappo-valid-tx: {{ hx_rgb(opts.colors.form.success) }};
            --wappo-input-col: {{ hx_rgb('#495057') }};
            --wappo-input-ph: {{ hx_rgb('#aeadad') }};
            --wappo-input-bor: {{ hx_rgb('#ced4da') }};
            --wappo-input-box-s: {{ hx_rgb(opts.colors.primary.bg, .25) }};
        }
        .wap-front .wbtn.wbtn-primary-light,
        .wap-front .wbtn.wbtn-primary-light:not(:disabled):not(.disabled):active, 
        .wap-front .wbtn.wbtn-primary-light:not(:disabled):not(.disabled).active {
            background: {{ hx_rgb(opts.colors.primary.bg, .8) }};
            background-color: {{ hx_rgb(opts.colors.primary.bg, .8) }};
            border-color: {{ hx_rgb(opts.colors.primary.bg, .6) }};
            color: var(--wappo-pri-tx);
            box-shadow: 0 .1em 0 {{ hx_rgb(opts.colors.primary.bg, .4) }};
        }
        .wap-front .wbtn.wbtn-primary.wbtn-booking,
        .wap-front .wbtn-primary.wbtn-booking:not(:disabled):not(.disabled):active, 
        .wap-front .wbtn-primary.wbtn-booking:not(:disabled):not(.disabled).active {
            font-size: {{ opts.button.slide_size+'em' }} !important;
            {{ buttonIsBold }}
            {{ buttonIsFull }}
        }
        .wap-front .wbtn.wbtn-primary,
        .wap-front .wbtn-primary:not(:disabled):not(.disabled):active, 
        .wap-front .wbtn-primary:not(:disabled):not(.disabled).active {
            background: var(--wappo-pri-bg);
            background-color: var(--wappo-pri-bg);
            border-color: {{ hx_rgb(opts.colors.primary.bg, .8) }};
            color: var(--wappo-pri-tx);
            box-shadow: 0 .1em 0 {{ hx_rgb(opts.colors.primary.bg, .6) }};
            margin: .2em 0;
        }
        .wap-front .d-section.ds-1 .wbtn.wbtn-primary{
            margin: .2em;
        }
        .wap-front .wbtn.wbtn-primary:hover {
            background-color: {{ hx_rgb(opts.colors.primary.bg,.9) }};
            box-shadow: 0 0 0 .2em {{ hx_rgb(opts.colors.primary.bg,.5) }};
        }

        .wap-front .wbtn:focus, 
        .wap-front .wbtn.focus {
            box-shadow: 0 0 0 .2em {{ hx_rgb(opts.colors.primary.bg, .25) }};
        }
        
        .wap-front .wbtn-primary.disabled, 
        .wap-front .wbtn-primary:disabled, 
        .wap-front .wbtn-primary.disabled:hover, 
        .wap-front .wbtn-primary:disabled:hover {
            background-color: var(--wappo-pri-bg) !important;
            border-color: {{ hx_rgb(opts.colors.primary.bg, .8) }} !important;
            color: var(--wappo-pri-tx) !important;
            box-shadow: 0 .1em 0 {{ hx_rgb(opts.colors.primary.bg, .6) }};
        }

        .wap-front .wbtn-primary:disabled:hover {
            background-color: var(--wappo-pri-bg) !important;
            border-color: {{ hx_rgb(opts.colors.primary.bg, .8) }} !important;
            color: var(--wappo-pri-tx) !important;
            box-shadow: 0 .1em 0 {{ hx_rgb(opts.colors.primary.bg, .6) }};
        }

        .wap-front .address-service {
            background-color: {{ hx_rgb(opts.colors.address.bg) }};
            border-color: {{ hx_rgb(opts.colors.address.bg, .8) }};
            color: {{ hx_rgb(opts.colors.address.text) }};
            box-shadow: 0 .1em 0 {{ hx_rgb(opts.colors.address.bg, .6) }};
        }
        .wap-front .address-service a{
            color: {{ hx_rgb(opts.colors.address.text) }};
        }

        .wap-front .wbtn.wbtn-secondary,
        .wap-front .wbtn.wbtn-secondary .wduration,
        .wap-front .wbtn-secondary:not(:disabled):not(.disabled):active, 
        .wap-front .wbtn-secondary:not(:disabled):not(.disabled).active {
            background: {{ hx_rgb(opts.colors.secondary.bg) }};
            background-color: {{ hx_rgb(opts.colors.secondary.bg) }};
            border-color: {{ hx_rgb(opts.colors.secondary.bg, .8) }};
            color: {{ hx_rgb(opts.colors.secondary.text) }};
            box-shadow: 0 .1em 0 {{ hx_rgb(opts.colors.secondary.bg, .6) }};
            margin: .4em 0;
        }

        .wap-front .wrap-calendar .wbtn.wbtn-secondary div{
            color: {{ hx_rgb(opts.colors.secondary.text) }};
        }

        .wap-front .wrap-calendar .wbtn.wbtn-secondary.selected div{
            color: {{ hx_rgb(opts.colors.secondary.text_selected) }};
        }

        .wap-front .wbtn.wbtn-secondary:hover {
            background-color: {{ hx_rgb(opts.colors.secondary.bg,.9) }};
            box-shadow: 0 0 0 .2em {{ hx_rgb(opts.colors.secondary.bg,.5) }};
        }

        .wap-front .wbtn.wbtn-secondary.selected{
            background-color: {{ hx_rgb(opts.colors.secondary.bg_selected) }};
            border-color: {{ hx_rgb(opts.colors.secondary.bg_selected, .8) }};
            color: {{ hx_rgb(opts.colors.secondary.text_selected) }};
            box-shadow: 0 .1em 0 {{ hx_rgb(opts.colors.secondary.bg_selected, .6) }};
        }
        .wap-front .wbtn.wbtn-secondary.selected svg{
            color: {{ hx_rgb(opts.colors.secondary.text_selected) }};
        }

        .wap-front .wap-form-body .timezone {
            background-color: {{ hx_rgb(opts.colors.body.bg, .9) }};
        }
        
        .wap-front .wap-form-body .no-avail, 
        .wap-front .wap-form-body .wbtn-link.disabled{
            color: {{ hx_rgb(opts.colors.body.disabled_links) }};
        }

        .wap-front .wap-head {
            background-color: {{ hx_rgb(opts.colors.header.bg) }};
        }
        .wap-front .wap-head.showall{
            background-color: {{ hx_rgb(opts.colors.header.bg, .9) }};
        }

        .wap-bf.show{
            border-radius: {{ calendarRound }}em;
        }
        
        .wap-front .wbtn.wbtn-secondary.dayselected{
            background-color: {{ hx_rgb(opts.colors.selected_day.bg) }};
        }
        .wap-front .wap-form-body .dayselected span.avail{
            color: {{ hx_rgb(opts.colors.selected_day.text) }};
        }

        .wap-front .wsummary-section {
            border-bottom: 1px solid {{ hx_rgb(opts.colors.body.text, .1) }};
        }
        

        .confirmation-summary span{
            color: {{ hx_rgb(opts.colors.body.text,.8) }};
        }
        .wtitle{
            color: {{ hx_rgb(opts.colors.body.text,.8) }};
            font-size: 1em;
            text-align:center;
        }
        .wdescription{
            color: {{ hx_rgb(opts.colors.body.text,.8) }};
        }
        

        .wap-front .wbtn-cell{
            border-radius: {{ calendarRound }}em;
        }

        .wap-front .form-control:focus {
            box-shadow: 0 0 0 1px {{ hx_rgb(opts.colors.primary.bg, .25) }};
        }
        .wap-front .wselected{
            background-color: {{ hx_rgb(opts.colors.primary.bg, .5) }};
            color: var(--wappo-pri-tx);
        }

        .wap-front [data-tt]:before {
            background-color: {{ hx_rgb(opts.colors.primary.bg, .8) }};
            color: var(--wappo-pri-tx);
        }

        .wap-front [data-tt]:after {
            border-top: 5px solid {{ hx_rgb(opts.colors.primary.bg, .9) }};
        }

        .wap-front .phone-field:focus-within {
            box-shadow: 0 0 2px  {{ hx_rgb(opts.colors.primary.bg, .25) }};
            border-color: var(--wappo-pri-bg);
        }
        .wap-front .field-required input:focus, 
        .wap-front .field-required select:focus {
            border-color: var(--wappo-pri-bg);
            box-shadow: 0 0 2px {{ hx_rgb(opts.colors.primary.bg, .6) }};
            outline: 2px solid transparent;
        }
        .wap-front .confirmation-cell .success {
            background-color: {{ hx_rgb(opts.colors.confirmation.bg) }};
        }
        
        .wap-booking-fields .isValid input[type="text"], 
        .wap-booking-fields .isValid input[type="email"], 
        .wap-booking-fields .isValid input[type="url"], 
        .wap-booking-fields .isValid input[type="tel"],
        .wap-booking-fields .isValid textarea{
            border-right: 4px solid {{ hx_rgb(opts.colors.form.success) }} !important;
        }


        .wap-wid.wclosable > .wclose::before, 
        .wap-wid.wclosable > .wclose::after {
            background-color: {{ hx_rgb(opts.colors.header.text, .7) }};
        }


        /** wrapper tag **/
        {{ '#'+wrapper }} .wap-front, 
        #wap-footer-container .wap-front {
            font-size: {{ baseFontSize }};
        }
        
    </v-style>
</template>

<script>
import Colors from '../Modules/Colors'
export default {
    mixins: [Colors],
    props: ['options', 'wrapper', 'largeVersion'],
    data:() => ({
        opts: null,
        widthWrapper: false,
        baseFontSize: '20px',
        calendarRound: .6,
    }),
    created(){
        this.opts = Object.assign ({}, this.options)

    },
    computed:{
        buttonIsBold(){
            return this.opts.button.check_bold === true ? 'font-weight:bold;':''
        },
        buttonIsFull(){
            return this.opts.button.check_full === true ? 'width:100%;':''
        },
    },
    mounted(){
        this.widthWrapper = document.getElementById(this.wrapper).clientWidth
        if(this.largeVersion){
            if(this.widthWrapper > '620'){
                this.baseFontSize = '22px'
            }else{
                this.baseFontSize = this.widthWrapper < '620' ? '21px':'20px'
            }
            
        }else{
            this.baseFontSize = '20px'
        }
        
        
    }
}
</script>
