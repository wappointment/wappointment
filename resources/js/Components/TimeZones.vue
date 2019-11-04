<template>
    <div :class="classWrapper" v-if="tzLoaded">
        <div v-if="typeAdvanced">
            <SearchDropdownTimezone v-model="timezone" :ph="labelDefault" :elements="timezones" 
             idKey="name" hasGroup :typeClass="typeClass"></SearchDropdownTimezone>
             <button v-if="viewingInDifferentTz" class="btn btn-link btn-xs" @click="backToStaffTimezone"> < <small> Back to {{ staffTimezone }}</small></button>
        </div>
        <select v-else class="form-control form-control-sm" v-model="timezone" id="timezoneselection">
            <option value="">{{ labelDefault }}</option>
            <optgroup v-for="(tzs, continent) in timezones" :label="continent">
                <option v-for="(zone, city) in tzs" :value="zone.name">{{ city }} [{{ zone.hours + ':' + zone.minutes }}]</option>
            </optgroup>
        </select>
    </div>
</template>

<script>
import SearchDropdownTimezone from '../Fields/SearchDropdownTimezone'
import momenttz from '../appMoment'
export default {
    components:{SearchDropdownTimezone},
    props: {
        timezones: null,
        defaultTimezone: '',
        staffTimezone: {
            type: String,
            default: ''
        },
        classW: {
            type: String,
            default: ''
        },
        typeAdvanced: {
            type: Boolean,
            default: true
        },
        wizard: {
            type: Boolean,
            default: false
        },
        typeClass: {
            type: String
        },
        labelDefault: {
            type: String,
            default: 'Select or search timezone'
        },
    },
    data() {
        return {
            timezone: '',
            classWrapper: 'form-group p-0 col-sm-4 col-lg-3 m-0',
            initSave: false, //allow to save on wizard initsetup,
            firstTrigger: true
        }
    },
    created(){
        if(this.wizard === true) this.initSave = true
        this.timezone = this.defaultTimezone == ''? momenttz.tz.guess():this.defaultTimezone
        if(this.classW!='') this.classWrapper = this.classW
    },
    methods:{
        backToStaffTimezone(){
            this.timezone = this.staffTimezone
        }
    },
    watch: {
        timezone: function (newTz, oldTz) {
            if(this.initSave === true){
                this.$emit('updateTimezone', newTz, this.firstTrigger)
                this.initSave = false
            }else{
                if(!this.firstTrigger) this.$emit('updateTimezone', newTz, this.firstTrigger)
            }
            this.firstTrigger = false

        }
    },
    computed: {
        tzLoaded(){
            return (this.timezones !== undefined) ? true:false
        },
        viewingInDifferentTz(){
            return this.staffTimezone !== '' && this.staffTimezone !== this.defaultTimezone
        }
    },
}
</script>
