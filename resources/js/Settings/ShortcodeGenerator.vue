
<template>
    <div class="d-flex align-items-center justify-content-between">
        <div class="commands-div">
            <div data-tt="Booking button's title"><label><InputPh v-model="titleGiven" ph="Button title"/></label></div>
            <div data-tt="Opens in a full screen popup"><label><input type="checkbox" v-model="popup"> Opens in a full screen popup</label></div>
            <div data-tt="Center the widget within the container"><label><input type="checkbox" v-model="center" :disabled="popup"> Center</label></div>
            <div data-tt="Opens the calendar's step automatically"><label><input type="checkbox" v-model="open"> Auto-open Calendar</label></div>
            <div data-tt="Calendar will expand to the container's width"><label><input type="checkbox" v-model="large" :disabled="popup"> Full width Calendar</label></div>
            <div data-tt="Show a week view instead of the full month"><label><input type="checkbox" v-model="week"> Week view</label></div>
            <div data-tt="The first screen in your booking form will be a staff selection page(if you have more than one staff)">
                <label :class="{'text-muted':staffSelected}"><input :disabled="staffSelected" type="checkbox" v-model="staffpage"> Select staff first</label></div>
            <template v-if="!simple">
                <div v-if="filteredCalendars" >
                    <SearchDropdown v-model="active_staff_id" ph="Lock Calendar/Staff" :elements="filteredCalendars" labelSearchKey="name"/>
                </div>
                <div v-if="filteredServices" class="mt-2" >
                    <SearchDropdown v-model="active_service_id" ph="Lock Service" :elements="filteredServices" labelSearchKey="name"/>
                </div>
            </template>
        </div>
        <div v-if="preview">
            <img :src="previewSCimg" class="img-fluid" alt="preview booking button shape" width="100" />
        </div>
        <span style="display:none;">{{ getShortCode }}</span>
    </div>
</template>

<script>
import RequestMaker from '../Modules/RequestMaker'
import ServiceCalendar from '../Services/V1/Calendars'
import SearchDropdown from '../Fields/SearchDropdown'
export default {
    mixins:[RequestMaker],
    components:{
        SearchDropdown,
    },
    props: {
        preview:{
            type:Boolean,
            default:true
        },
        title:{
            type:String,
            default:''
        },
        calendar_id: {
            default: false
        },
        service_id: {
            default: false
        },
        calendars: {
            default: null
        },
        services: {
            default: null
        },
        simple: {
            default: false
        },
        canLockStaff: {
            default: false
        },
        canLockService: {
            default: false
        },
    },
    data: () => ({
        large:false,
        open:false,
        popup:false,
        center: false,
        week:false,
        staffpage: false,
        titleGiven: '',
        active_staff_id: false,
        active_service_id: false,
        calendars_data:null,
        services_data:null,
        servicesService: null,
        calendarsService: null,
    }),
    created(){
        this.titleGiven = this.title
        this.active_staff_id = this.calendar_id
        this.active_service_id = this.service_id
        this.calendarsService = this.$vueService(new ServiceCalendar)
        
        if(!this.services || !this.calendars){
            this.loadCalendars()
        }else{
            this.setBaseData(this.calendars, this.services)
        }
        
    },
    methods:{
        setBaseData(calendars, services){
            this.calendars_data = calendars
            this.services_data = services
        },
        loadCalendars() { // overriding
            this.request(this.requestCalendars, {}, undefined, false, this.loadedCalendars)
        },

        loadedCalendars(response){
            this.setBaseData(response.data.calendars, response.data.services)
        },

        async requestCalendars(params){
           return await this.calendarsService.call('index', params)
        },

    },
    watch:{
        active_staff_id(val){
            if([undefined, false,''].indexOf(val) === -1){
                this.staffpage = false
            }
        },
        popup(val){
            if(val){
                this.center = false
                this.large = false
            }
        }
    },
    computed:{
        staffSelected(){
            return [undefined, false,''].indexOf(this.active_staff_id) === -1
        },
        filteredCalendars(){
            if(this.active_service_id && this.calendars_data){
                let service_id = this.active_service_id
                return this.calendars_data.filter((e) => e.services.indexOf(service_id) !== -1)
            }
            return this.calendars_data
        },
        filteredServices(){
            if(this.active_staff_id && this.services_data){
                let staff_id = this.active_staff_id
                let services_allowed = this.calendars_data.find((e) => e.id == staff_id).services
                return this.services_data.filter((e) => services_allowed.indexOf(e.id) !== -1)
            }
            return this.services_data
        },
        getShortCode(){
            let shortcode = 'wap_widget title="'+this.titleGiven+'"'
            shortcode += this.large? ' large ':'' 
            shortcode += this.open? ' open ':''
            shortcode += this.popup? ' popup ':''
            shortcode += this.center? ' center ':''
            shortcode += this.week? ' week ':''
            shortcode += this.staffpage? ' staff_page ':''
            shortcode += this.active_staff_id? ' staff="'+this.active_staff_id+'" ':''
            shortcode += this.active_service_id? ' service="'+this.active_service_id+'" ':''
            shortcode = '['+shortcode+']'
            
            this.$emit('change', shortcode, {
                largeVersion: this.large,
                autoOpen: this.open,
                popup: this.popup,
                week: this.week,
                center: this.center,
                staffPage: this.staffpage,
                buttonTitle: this.titleGiven
            })
            return shortcode
        },
        previewSCimg(){
            let image = 'widget_' + (this.open ? 'cal_':'') + (this.large ? 'lg':(this.center ? 'sm-c':'sm')) + '.svg'
            return window.apiWappointment.resourcesUrl +'images/' + image
        },
    }
}   
</script>
<style >
.commands-div{
    min-width:280px;
}
</style>