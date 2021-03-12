<template>
    <div>
        <div v-if="calendarListing">
            <button @click="showCalendar" class="btn btn-outline-primary btn my-2">Add new</button>
            <div class="table-hover" v-if="loadedData">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Weekly Availability</th>
                            <th scope="col" v-if="!elements.db_required">Services</th>
                            <th scope="col">Integrations</th>
                            <th scope="col">Connected calendars</th>
                        </tr>
                    </thead>
                    <draggable @change="orderChanged" v-model="elements.calendars" draggable=".row-click" handle=".dashicons-move" tag="tbody" v-if="elements.calendars.length > 0">

                        <tr class="row-click" v-for="(calendar, idx) in elements.calendars">
                            <td>
                                {{ idx + 1 }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="calendar-pic" :class="[calendar.status == 1 ? 'active':'inactive']">
                                        <img :src="calendar.avatar" class="img-fluid wrounded" width="40" :alt="calendar.name" />
                                        <span role="button" class="status" :data-tt="[calendar.status == 1 ? 'Active':'Inactive']" @click="toggleStatus(calendar,idx)"></span>
                                    </div>
                                    <div class="ml-2">
                                        <div>{{ calendar.name }}</div>
                                        <small>{{ calendar.timezone }}</small>
                                    </div>
                                    <div class="actions ml-4 text-muted">
                                        <span data-tt="Sort"><span class="dashicons dashicons-move"></span></span>
                                        <!-- <span data-tt="Delete"><span class="dashicons dashicons-trash" @click.prevent.stop="deleteService(locationObj.id)"></span></span>
                                        <span>(id: {{ locationObj.id }})</span> -->
                                    </div>
                                </div>
                            </td>
                            <td>
                                <CalendarsRegav @edit="editAvailability" :calendar="calendar" />
                            </td>
                            <td v-if="!elements.db_required">
                                <div class="d-flex" role="button" v-if="calendar.services.length>0" @click="editServices(calendar)">
                                    <ValueCard v-for="serviceid in calendar.services" :key="serviceid">{{ displayServiceName(serviceid,elements.services) }} </ValueCard>
                                </div>
                                <button class="btn btn-xs btn-outline-primary" @click="editServices(calendar)">Add services</button>
                            </td>
                            <td>
                               <Connections :connections="calendar.connected.services === undefined ? []:calendar.connected.services"/>
                               <a v-if="calendar.connected" href="javascript:;" class="small" @click="goToDotCom(calendar)">edit</a>
                               <button v-else class="btn btn-xs btn-outline-primary tt-lg mt-2" @click="goToDotCom(calendar)">Connect Account</button>
                            </td>
                            <td>
                               <div class="d-flex" v-if="getExternals(calendar)">
                                   <div class="unclickable cal-icon" v-for="(single_url, calendar_id) in getExternals(calendar)" >
                                        <div class="dashicons dashicons-calendar-alt"></div>
                                        <div class="card cardb p-2 px-3 mt-1 cal-desc">
                                            <div class="d-flex flex-row justify-content-between ">
                                                <div>
                                                    <div class="small text-primary">{{ single_url}}</div>
                                                    <p class="vsmall text-muted m-0">
                                                    Last checked: <span class="data-item">{{ lastChecked(calendar_id, calendar) }}</span> | 
                                                    Last changed: <span class="data-item">{{ lastChanged(calendar_id, calendar) }}</span> | 
                                                    Process duration: <span class="data-item">{{ calDuration(calendar_id, calendar) }}</span></p>
                                                </div>
                                                <button class="align-self-start btn btn-xs btn-link hidden" data-tt="Disconnect Calendar" @click="disconnectCalendar(calendar_id, calendar.id)"><span class="dashicons dashicons-dismiss"></span></button>
                                            </div>  
                                        </div>
                                    </div>
                                    <a class="small d-flex align-items-center" href="javascript:;" @click="refreshManually(calendar.id)"><span class="dashicons dashicons-update"></span> Refresh all</a>
                                </div>
                                <div>
                                    <button class="btn btn-xs btn-outline-primary tt-lg" v-if="!calendarLimitReached(calendar)"
                                    @click="goToSync(calendar)" data-tt="Make sure clients can't book you when you're busy">Connect external calendar</button>
                                </div>
                            </td>
                        </tr>

                    </draggable>
                     <tbody v-else>
                         <tr>
                            You don't have any calendars yet
                        </tr>
                     </tbody>
                </table>
            </div>

        </div>
        <div v-if="calendarAdd">
            <button class="btn btn-link btn-xs mb-2" @click="showListing"> < Back</button>
            <CalendarsAddEdit :calendar="elementPassed" :timezones_list="elements.timezones_list" 
            :staffs="elements.staffs" :services="elements.services"
            @saved="hasBeenSavedDeleted"/>
        </div>
        <div v-if="calendarRegav">
            <button class="btn btn-link btn-xs mb-2" @click="showListing"> < Back</button>
            <WeeklyAvailability :calendar="elementPassed" :timezones_list="elements.timezones_list" :staffs="elements.staffs"/>
        </div>
        <WapModal v-if="showModal" :show="showModal" @hide="hideModal" large noscroll>
            <h4 slot="title" class="modal-title"> 
                <span>Connect Personal calendar</span>
            </h4>
            <CalendarsExternal :calendar_id="calendar_main_id" @savedSync="savedSync" @errorSaving="errorSavingCalendar" noback />
            
        </WapModal>
        <WapModal v-if="dotcomOpen" :show="dotcomOpen!==false" large @hide="dotcomOpen = false">
            <h4 slot="title" class="modal-title"> 
                Connect to Zoom, Google Calendar etc...
            </h4>
            <CalendarsIntegrations @reload="reloadListing" :calendar="dotcomOpen" />
        </WapModal>
        <WapModal v-if="editingServices" :show="editingServices!==false" large @hide="editingServices = false">
            <h4 slot="title" class="modal-title"> 
                Edit services allowed
            </h4>
            <SearchDropdown v-model="editingServices.services" hasMulti ph="labelDefault" :elements="elements.services" 
                idKey="id" labelSearchKey="name"></SearchDropdown>
                <button class="btn btn-primary" @click="saveServices">Save</button>
        </WapModal>
    </div>
</template>

<script>

import ValueCard from '../Fields/ValueCard'
import ServiceCalendar from '../Services/V1/Calendars'
import CalendarsAddEdit from './CalendarsAddEdit'
import WeeklyAvailability from '../RegularAvailability/Edit'
import Connections from '../RegularAvailability/Connections'
import CalUrl from '../Modules/CalUrl'
import SettingsSave from '../Modules/SettingsSave'
import CalendarsExternal from './CalendarsExternal'
import CalendarsIntegrations from './CalendarsIntegrations'
import CalendarsRegav from './CalendarsRegav'
import DurationCell from '../BookingForm/DurationCell'
import AbstractListing from '../Views/AbstractListing'
import SearchDropdown from '../Fields/SearchDropdown'
export default {
    extends: AbstractListing,
    components:{
        ValueCard,
        DurationCell,
        CalendarsAddEdit,
        WeeklyAvailability,
        Connections,
        CalendarsExternal,
        CalendarsIntegrations,
        CalendarsRegav,
        SearchDropdown
    },
    mixins:[CalUrl, SettingsSave],
    data: () => ({
        currentView: 'listing',
        viewName:'empty',
        elementPassed: null,
        calendarsOrder: [],
        showModal: false,
        dotcomOpen: false,
        calendar_main_id: false,
        editingServices: false
    }),
    created(){
        this.mainService = this.$vueService(new ServiceCalendar)
        
    },
    computed: {
        calendarListing(){
            return this.currentView == 'listing'
        },
        calendarAdd(){
            return ['add','edit'].indexOf(this.currentView) !== -1
        },
        calendarRegav(){
            return this.currentView == 'regav'
        }
    },
    methods: {
        saveServices(){
            this.request(this.saveServicesRequest,this.editingServices, undefined, false, this.closeRefresh)
        },

        closeRefresh(){
            this.editingServices = false
            this.hasBeenSavedDeleted()
        },

        async saveServicesRequest(params){
           return await this.mainService.call('saveService',params)
        },
        editServices(calendar){
            this.editingServices = calendar
        },
        displayServiceName(id,services) {
            return services.find(e => e.id ==id).name
        },

        reloadListing(){
            this.hideModal()
            this.showListing()
            this.loadElements()
        },
        afterLoaded(){
            if(this.$route.params.id){
                this.currentView = 'edit'
                let cal_id = this.$route.params.id
                this.editAvailability(this.elements.calendars.find(e => e.id == cal_id), false)
                
            }else if(this.$route.name == 'calendars_zoom_account'){
                this.goToDotCom(this.elements.calendars[0])
            }
        },
        getExternals(calendar){
            if(calendar.calendar_urls!== undefined){
                return calendar.calendar_urls!== false && Object.keys(calendar.calendar_urls).length > 0 ? calendar.calendar_urls:false
            }else{
                return calendar.options.calendar_urls!== false && Object.keys(calendar.options.calendar_urls).length > 0? calendar.options.calendar_urls:false
            }
        },
        editAvailability(calendar, manual = true){
            this.elementPassed = calendar
            this.currentView = this.elements.db_required ? 'regav':'edit'
            if(!this.elements.db_required && manual){
                this.$router.push({name:'calendars_edit', params:{id:calendar.id}})
            }
        },
        hideModal(){
            this.showModal = false
            this.dotcomOpen = false
        },
        goToSync(calendar) {
            if(this.calendarLimitReached(calendar)){
                return
            }
            this.calendar_main_id = calendar.id
            this.showModal = true
        },
        goToDotCom(calendar){
            this.dotcomOpen = calendar
        },
        calendarLimitReached(calendar){
            return calendar.calendar_urls!== false && Object.keys(calendar.calendar_urls).length > 3
        },
        
        loadElements() { // overriding
            if(this.currentView == 'listing') {
                this.request(this.requestElements, {}, undefined, false, this.loadedElements, this.failedLoadingElements)
            }
        },
        orderChanged(val){
            this.request(this.reorderRequest,{ id:val.moved.element.id, 'new_sorting':val.moved.newIndex }, undefined, false, this.hasBeenSavedNoReload)
        },
        async reorderRequest(params){
           return await this.mainService.call('reorder',params)
        },
        hasBeenSavedNoReload(result){
            return this.hasBeenSavedDeleted(result, false)
        },
        hasBeenSavedDeleted(result, reload = true){
            if(reload) {
                this.reloadListing()
            }

        },
        deleteCalendar(calendar_id, calendar_main_id){
            this.$WapModal().confirm({
                title: 'Do you really want to delete this calendar?',
            }).then((response) => {
                if(response !== false){
                    this.request(this.deleteRequest,{id:calendar_id, staff_id: calendar_main_id},undefined,false,this.hasBeenSavedDeleted)
                }
            })
        },
        toggleStatus(calendar, idx){
           return this.elements.db_required ? 
            this.runDbUpdate():this.request(this.toggleRequest,{ id:calendar.id}, undefined, false, this.hasBeenToggled.bind(null,idx))
        },
        hasBeenToggled(idx, response){
            let calendarsSaved = this.elements.calendars
            calendarsSaved[idx].status = calendarsSaved[idx].status == 1 ? 0:1
            this.elements.calendars = []
            setTimeout(this.reFeedCalendars.bind(null,calendarsSaved), 100);
        },
        reFeedCalendars(calendarsSaved){
            this.elements.calendars = calendarsSaved
        },
        async toggleRequest(params){
           return await this.mainService.call('toggle',params)
        },
        disconnectCalendar(calendar_id, calendar_main_id){
            this.$WapModal().confirm({
                title: 'Confirm calendar disconnection?',
            }).then((response) => {

                if(response === false){
                    return
                }

                this.request(this.disconnectCalendarRequest, {calendar_id: calendar_id,  staff_id: calendar_main_id}, undefined,false, this.disconnectCalendarSuccess)
            }) 
            
            
        },
        async disconnectCalendarRequest(params) {
            return await this.mainService.call('disconnectCal', params) 
        },
        disconnectCalendarSuccess(response){
            this.$WapModal().notifySuccess(response.data.message)
            this.reloadListing()
        },

        refreshManually(id){
            this.request(this.refreshCalendarsRequest, {staff_id: id}, undefined,false, this.disconnectCalendarSuccess)
        },
        async refreshCalendarsRequest(params) {
            return await this.mainService.call('refreshCalendars', params)
        },

        async deleteRequest(params){
           return await this.mainService.call('delete',params)
        },
        editElement(element){
            if(this.crumb){
                this.$emit('updateCrumb',[
                    { target: 'goToMain', label: 'General'},
                    { target: 'goToCalendar', label: 'Calendars', subview: 'listing' },
                    { target: 'goToCalendarAdd', label: 'Edit' , disabled:true},
                ], 'edit', {element:element})
            }else{
                this.currentView = 'edit'
                this.elementPassed = element
            }
        },
        showCalendar(){
            if(this.elements.db_required){
                return this.runDbUpdate()
            }
            if(this.elements.limit_reached !== false){
                return this.requiresAddon('staff', this.elements.limit_reached)
            }

            if(this.crumb){
                this.$emit('updateCrumb',[
                    { target: 'goToMain', label: 'General'},
                    { target: 'goToCalendar', label: 'Calendars', subview: 'listing' },
                    { target: 'goToCalendarAdd', label: 'Add' , disabled:true},
                ], 'add')
            }else{
                this.currentView = 'add'
                this.elementPassed = {
                  avatar:'',
                  gravatar: '',
                  name: '',
                  id: '',
                  timezone: this.elements.staffDefault.timezone,
                  regav: this.elements.staffDefault.regav,
                  avb: this.elements.staffDefault.availaible_booking_days
              }
            }
            
        },
        showListing(){
            if(this.crumb){
                this.$emit('updateCrumb',[
                    { target: 'goToMain', label: 'General'},
                    { target: 'goToCalendar', label: 'Calendars' , disabled:true},
                ],'listing')
              }else{
                this.currentView = 'listing'
                this.elementPassed = null
            }

        },

    }
}   
</script>
<style>
.calendar-pic{
    position: relative;
}
.calendar-pic .status{
    display: block;
    width: .7em;
    height: .7em;
    border-radius: 50%;
    position: absolute;
    top: 30px;
    left: 30px;
}
.calendar-pic.active .status{
    background-color:var(--primary);
}
.calendar-pic.inactive .status{
    background-color:#ccc;
}
.calendar-pic.inactive img{
    filter: grayscale(1);
    opacity:.4;
}
.location {
    margin: .2rem;
    color: #717171;
}
.cal-icon{
    position: relative;
}
.cal-icon .cal-desc{
    display: none !important;
}
.cal-icon:hover .cal-desc{
    display: flex !important;
    position: absolute;
    right: 0px;
    z-index: 9;
    top: 16px;
}
.cal-icon:hover > .dashicons {
    color: var(--primary);
}
</style>