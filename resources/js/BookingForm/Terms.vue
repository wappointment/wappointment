<template>
    <div v-if="termsIsOn" class="wap-terms" v-html="getTerms"></div>
</template>

<script>


export default {
    props: {
        options:{}, 
        tos:{
            type:Boolean,
            default:false
        }
    },
    computed: {
        getTerms(){
            return this.tos ? this.getTos: this.getPP
        },
        termsIsOn(){
            return this.getCheck === true
        },
        
        getTos(){
            return this.getPP
            .replace('[linktos]', '<a href="'+this.cleanString(this.options.swift_payment.tos_link)+'" target="_blank">')
            .replace('[/linktos]', '</a>')
        },
        getPP(){
            return this.cleanString(this.getText)
            .replace('[link]', '<a href="'+this.cleanString(this.options.form.terms_link)+'" target="_blank">')
            .replace('[/link]', '</a>')
        },

        getCheck(){
            return this.tos ? this.options.swift_payment.check_tos:this.options.form.check_terms
        },
        getText(){
            return this.tos? this.options.swift_payment.tos_text:this.options.form.terms
        },
    },
}
</script>
<style>

.wap-terms{
    font-size: .7em;
    text-align: left;
    line-height: 1.4;
    margin-bottom: .3em;
}

</style>
