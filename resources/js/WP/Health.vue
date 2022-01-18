<template>
    <div class="wappo-health" :class="{'show':hasIssue}">
        <template v-if="lang">
            <h5>{{ lang.cron_found_issue }}<a href="javascript:;" @click="checkHealth" ><span class="small dashicons dashicons-image-rotate"></span></a></h5>
            <div class="health-checks">
                <div v-for="key in Object.keys(checks)" class="d-flex small" v-if="showall || (!showall && checks[key])">
                    <div>{{ getLabel(key)}}</div>
                    <span v-if="checks[key]" class="dashicons dashicons-no text-danger"></span>
                    <span v-else class="dashicons dashicons-yes text-success"></span>
                    <a class="class" v-if="hasSolution(key)" :href="hasSolution(key)" target="_blank">{{ lang.cron_read }}</a>
                </div>
                <a href="javascript:;" class="small" @click="showall=!showall">{{ lang.cron_showall }}</a>
            </div>
        </template>
    </div>
</template>
<script>
import RequestMaker from '../Modules/RequestMaker'
import AppService from '../Services/V1/App'
export default {
    mixins:[RequestMaker],
    data: () => ({
        checks: false,
        lang: null,
        showall: false,
        solutions: false,
    }),
    created(){
        this.mainService = this.$vueService(new AppService)
        if(window.apiWappointment.wp_user.permissions.indexOf('switch_themes') !== -1){
            this.checkHealth()
        }
        
    },
    computed:{
        hasIssue(){
            return Object.values(this.checks).find(e => e === true)
        },
        unreliableScheduled(){
            return this.checks.cron_unreliable || this.checks.late_run || this.checks.late_tasks
        }
    },
    methods: {
        getLabel(key){
            return this.lang[key]
        },
        hasSolution(key){
            return this.solutions[key]
        },
        checkHealth(){
            this.request(this.checkHealthRequest, {}, undefined, false, this.successCheck)
        },
        async checkHealthRequest(){
          return await this.mainService.call('health') 
        },
        successCheck(result){
            this.lang = result.data.lang
            this.checks = result.data.checks
            this.data = result.data.data
            this.solutions = result.data.solutions
        }

    }
}
</script>
<style>
.wappo-health{
    margin: 0 auto;
    padding: .5rem;
    border: 1px solid #ededed;
    color: #6868aa;
    border-radius: 1rem 1rem 0 0;
    bottom: -200px;
    background-color: #fff;
    border-bottom: 0;
    transition: all .5s ease-in-out;
    opacity: 0;
    box-shadow: 0 0 0 0 rgba(0,0,0,.1);
    max-width: 500px;
    right:0;
    padding: 1rem;
    position:fixed;
    color: #6f6f6f;
    z-index: 2;
}

.wappo-health.show{
    bottom: 0;
    opacity: 1;
    box-shadow: 0 .4rem 1rem 0 rgba(0,0,0,.1);
}
.health-checks {
  background-color: #f0f0f0;
  padding: .4rem;
  border-radius: .5rem;
}
</style>
