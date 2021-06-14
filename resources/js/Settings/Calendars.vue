<template>
    <div>
        <div v-if="calendarListing">
            <div class="d-flex align-items-center">
                <button v-if="isUserAdministrator" @click="showCalendar" class="btn btn-outline-primary btn my-2">Add new</button>
                <InputPh class="max-200 ml-2 mb-0" v-if="loadedData && elements.calendars.length > 10" type="text" v-model="searchterm" ph="Search name" />
            </div>
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

                        <tr class="row-click" v-for="(calendar, idx) in filteredSearchable">
                            <td>
                                {{ idx + 1 }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="calendar-pic" :class="[calendar.status == 1 ? 'active':'inactive']">
                                        <img :src="calendar.avatar" class="wrounded" width="40" :alt="calendar.name" />
                                        <span v-if="canCalUnpublish" role="button" class="status" :data-tt="[calendar.status == 1 ? 'Active':'Inactive']" @click="toggleStatus(calendar,idx)"></span>
                                    </div>
                                    <div class="ml-2">
                                        <div>{{ calendar.name }}</div>
                                        <small>{{ calendar.timezone }}</small>
                                    </div>
                                </div>
                                <div class="wlist-actions text-muted" v-if="isUserAdministrator">
                                    <span data-tt="Sort" v-if="searchterm=='' && elements.calendars.length > 1"><span class="dashicons dashicons-move"></span></span>
                                    <span data-tt="Edit"><span class="dashicons dashicons-edit" @click.prevent.stop="editAvailability(calendar)"></span></span>
                                    <span data-tt="Delete"><span class="dashicons dashicons-trash" @click.prevent.stop="deleteCalendar(calendar.id)"></span></span>
                                    <span data-tt="Get Shortcode"><span class="dashicons dashicons-shortcode" @click.prevent.stop="getShortCode(calendar.id)"></span></span>
                                    <span data-tt="Set Permissions" v-if="isStaffCalendar(calendar)"><span class="dashicons dashicons-unlock" @click.prevent.stop="editPermission(calendar)"></span></span>
                                    <span data-tt="Set Custom Field" v-if="elements.allowStaffCf" ><span class="dashicons dashicons-editor-code" @click.prevent.stop="setCustomFields(calendar)"></span></span>
                                    <span>(id: {{ calendar.id }})</span>
                                </div>
                            </td>
                            <td>
                                <CalendarsRegav :canEdit="canCalEditWeekly" @edit="editAvailability" :calendar="calendar" />
                            </td>
                            <td v-if="!elements.db_required" class="cell-services">
                                <div class="d-flex flex-wrap" role="button" v-if="calendar.services.length>0">
                                    <ValueCard v-for="serviceid in calendar.services" :key="serviceid" :canDiscard="false">{{ displayServiceName(serviceid, elements.services) }} </ValueCard>
                                </div>
                                <button v-if="canCalEditServices" class="btn btn-xs btn-outline-primary" @click="editServices(calendar)">Edit services</button>
                            </td>
                            <td>
                               <Connections :connections="calendar.connected.services === undefined ? []:calendar.connected.services"/>
                               <div v-if="canCalConnectAccount">
                                   <a v-if="calendar.connected" href="javascript:;" class="small" @click="goToDotCom(calendar)">edit</a>
                                    <button v-else class="btn btn-xs btn-outline-primary tt-lg mt-2" @click="goToDotCom(calendar)">Connect Account</button>
                               </div>
                               
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
                                                <button v-if="canCalDelIcs" class="align-self-start btn btn-xs btn-link hidden" data-tt="Disconnect Calendar" @click="disconnectCalendar(calendar_id, calendar.id)"><span class="dashicons dashicons-dismiss"></span></button>
                                            </div>  
                                        </div>
                                    </div>
                                    <a class="small d-flex align-items-center" href="javascript:;" v-if="canCalAddIcs" @click="refreshManually(calendar.id)"><span class="dashicons dashicons-update"></span> Refresh all</a>
                                </div>
                                <div>
                                    <button class="btn btn-xs btn-outline-primary tt-lg" v-if="canCalAddIcs && !calendarLimitReached(calendar)"
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
            :staffs="elements.staffs" :services="elements.services" :calendarsUsed="calendarsUsed"
            @saved="hasBeenSavedDeleted"/>
        </div>
        <div v-if="calendarRegav">
            <button class="btn btn-link btn-xs mb-2" @click="showListing"> < Back</button>
            <WeeklyAvailability :calendar="elementPassed" :timezones_list="elements.timezones_list" :staffs="elements.staffs"/>
        </div>

        <WapModal v-if="showModal" :show="showModal" @hide="hidePopup" noscroll>
            <h4 slot="title" class="modal-title"> {{ modalTitle }} </h4>
            <ShortcodeDesigner v-if="showShortcode" :calendar_id="showShortcode" :calendars="elements.calendars" :services="elements.services" :showTip="false" />
            <StaffPermissionsManager v-if="showPermissions" :permissions="elements.permissions" :user="showPermissions" @save="savePermissions" />
            <StaffCustomFieldEditor v-if="editCustomField" :staff="editCustomField" @save="saveCustomFields" />
            <StaffAssignServices v-if="editingServices" @save="saveServices" :user="editingServices" :current="editingServices.services" :services="elements.services" />
            <StaffCalendarsIntegrations v-if="dotcomOpen" @reload="reloadListing" :calendar="dotcomOpen" />
            <StaffCalendarsExternal v-if="editingExternal" :user="editingExternal" :calendar_id="editingExternal.id" @savedSync="reloadListing" noback />
        </WapModal>

    </div>
</template>

<script>

import AbstractListing from '../Views/AbstractListing'
import ValueCard from '../Fields/ValueCard'
import ServiceCalendar from '../Services/V1/Calendars'
import CalendarsAddEdit from './CalendarsAddEdit'
import WeeklyAvailability from '../RegularAvailability/Edit'
import Connections from '../RegularAvailability/Connections'
import CalUrl from '../Modules/CalUrl'
import SettingsSave from '../Modules/SettingsSave'
import CalendarsRegav from './CalendarsRegav'
import DurationCell from '../BookingForm/DurationCell'
import ShortcodeDesigner from './ShortcodeDesigner'
import StaffAssignServices from './StaffAssignServices'
import StaffPermissionsManager from './StaffPermissionsManager'
import StaffCalendarsIntegrations from './StaffCalendarsIntegrations'
import StaffCalendarsExternal from './StaffCalendarsExternal'
import StaffCustomFieldEditor from './StaffCustomFieldEditor'
import hasPermissions from '../Mixins/hasPermissions'
import isSearchable from '../Mixins/isSearchable'
import HasPopup from '../Mixins/HasPopup'

export default {
    extends: AbstractListing,
    components:{
        ValueCard,
        DurationCell,
        CalendarsAddEdit,
        WeeklyAvailability,
        Connections,
        CalendarsRegav,
        ShortcodeDesigner,
        StaffCalendarsExternal,
        StaffCalendarsIntegrations,
        StaffPermissionsManager,
        StaffAssignServices,
        StaffCustomFieldEditor
    },
    mixins:[CalUrl, SettingsSave, hasPermissions, isSearchable, HasPopup],
    data: () => ({
        currentView: 'listing',
        viewName:'empty',
        elementPassed: null,
        calendarsOrder: [],
        showShortcode: false,
        dotcomOpen: false,
        editingExternal: false,
        editingServices: false,
        showPermissions: false,
        editCustomField: false,
    }),
    created(){
        this.mainService = this.$vueService(new ServiceCalendar)
    },
    computed: {
        searchable(){
            return this.elements.calendars
        },
        calendarListing(){
            return this.currentView == 'listing'
        },
        calendarAdd(){
            return ['add','edit'].indexOf(this.currentView) !== -1
        },
        calendarRegav(){
            return this.currentView == 'regav'
        },
        onlyOneCalendarEditable(){
            return this.elements.calendars.length === 1
        },
        canCalEditServices(){
            return this.canCalendarEdit('wappo_self_services')
        },
        canCalConnectAccount(){
            return this.canCalendarEdit('wappo_self_connect_account')
        },
        canCalAddIcs(){
            return this.canCalendarEdit('wappo_self_add_ics')
        },
        canCalDelIcs(){
            return this.canCalendarEdit('wappo_self_del_ics')
        },
        canCalEditWeekly(){
            return this.canCalendarEdit('wappo_self_weekly')
        },
        canCalUnpublish(){
            return this.canCalendarEdit('wappo_self_unpublish')
        },
        calendarsUsed(){
            return this.searchable.map(e => e.wp_uid)
        }
    },
    methods: {
        canCalendarEdit(something){
            return this.isUserAdministrator || (this.onlyOneCalendarEditable && this.hasPermission(something))
        },
        isStaffCalendar(calendar){
            return parseInt(calendar.wp_uid) > 0 && calendar.roles.indexOf('administrator') === -1 && calendar.roles.indexOf('wappointment_staff') === -1
        },
        getShortCode(calendar_id){
            this.showShortcode = calendar_id
            this.openPopup('Get Booking Widget Shortcode')
        },
        goToSync(calendar) {
            if(this.calendarLimitReached(calendar)){
                return
            }
            this.editingExternal = calendar
            this.openPopup('Connect Personal calendar')
        },
        goToDotCom(calendar){
            this.dotcomOpen = calendar
            this.openPopup('Connect to Zoom, Google Calendar etc...')
        },
        setCustomFields(calendar){
            this.editCustomField = calendar
            this.openPopup('Set custom Fields')
        },
        
        editServices(calendar){
            this.editingServices = calendar
            this.openPopup('Edit services allowed')
        },

        editPermission(calendar){
            this.showPermissions = calendar
            this.openPopup('Edit user permissions')
        },

        hideElementsPopup(modalTitle){
            this.showShortcode = false
            this.editingExternal = false
            this.dotcomOpen = false
            this.editingServices = false
            this.showPermissions = false
            this.editCustomField = false
        },
        saveCustomFields(customFields, fieldsValues, deletedFields){
            this.request(this.saveCustomFieldsRequest, {id: this.editCustomField.id, custom_fields:customFields, values:fieldsValues, deleted:deletedFields}, undefined, false, this.closeRefresh)
        },
         async saveCustomFieldsRequest(params){
           return await this.mainService.call('saveCustomFields',params)
        },

        saveServices(changedServices){
            this.request(this.saveServicesRequest, {id: this.editingServices.id, services:changedServices}, undefined, false, this.closeRefresh)
        },

        async saveServicesRequest(params){
           return await this.mainService.call('saveService',params)
        },

        savePermissions(new_permissions){
            this.request(this.savePermissionsRequest, {id:this.showPermissions.id, permissions: new_permissions}, undefined, false, this.closeRefresh)
        },

        async savePermissionsRequest(params){
           return await this.mainService.call('savePermission',params)
        },

        closeRefresh(){
            this.hidePopup()
            this.hasBeenSavedDeleted()
        },
        
        displayServiceName(id,services) {
            let service_found = services.find(e => e.id ==id)
            return service_found!== undefined ? service_found.name:'Undefined service'
        },

        reloadListing(){
            this.hidePopup()
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
        deleteCalendar(calendar_id){
            this.$WapModal().confirm({
                title: 'Do you really want to delete this calendar?',
            }).then((response) => {
                if(response !== false){
                    this.request(this.deleteRequest,{id:calendar_id},undefined,false,this.hasBeenSavedDeleted)
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
                this.$router.push({name:'calendars'})
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
    word-break:break-all;
}
.cal-icon:hover > .dashicons {
    color: var(--primary);
}

.cell-services{
    max-width: 200px;
}
.cell-services .d-flex{
    max-height: 44px;
    overflow: hidden;
    margin-bottom: .4em;
}
.cell-services .d-flex:hover{
    max-height: none !important;
}
</style>