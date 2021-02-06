<template>
    <div >
        <div v-if="calendarListing">
            <button @click="showCalendar" class="btn btn-outline-primary btn my-2">Add new</button>
            <div class="table-responsive table-hover" v-if="loadedData">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Weekly Availability</th>
                            <th scope="col">Services</th>
                            <th scope="col">Connections</th>
                            <th scope="col">External calendars</th>
                        </tr>
                    </thead>
                    <draggable @change="orderChanged" v-model="elements.calendars" draggable=".row-click" handle=".dashicons-move" tag="tbody" v-if="elements.calendars.length > 0">

                        <tr  class="row-click" v-for="(calendar, idx) in elements.calendars">
                            <td>
                                <div @mouseover="">{{ idx + 1 }} </div> 
                            </td>
                            <td>
                                <div class="d-flex">
                                    <div><img :src="calendar.avatar" class="img-fluid wrounded" width="40" /></div>
                                    <div class="ml-2">
                                        <div>{{ calendar.name }}</div>
                                        <small>{{ calendar.timezone }}</small>
                                    </div>
                                </div>
                                
                            </td>
                            <td>
                                <div class="d-flex">
                                    <div v-for="(hours,day) in getUsedDays(calendar.regav)"  class="dayLabel mr-2">
                                        <div :data-tt="getDayLabel(day)">{{ getDayLabel(day)[0] }}</div>
                                        <div class="d-flex">
                                            <div class="available-slot mr-1" v-for="hour in hours" :data-tt="convertHours(hour)"></div>
                                        </div>
                                    </div>
                                </div>
                                <a href="javascript:;" @click="editAvailability(calendar)">edit</a>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <div v-for="service in calendar.services">
                                        {{ service.name }}
                                    </div>
                                </div>
                            </td>
                            <td>
                               <Connections :connections="calendar.connected.services"/>
                            </td>
                            <td>
                               <div v-if="calendar.calendar_urls!== false && Object.keys(calendar.calendar_urls).length > 0" class="card cardb p-2 px-3 mt-1 unclickable" v-for="(single_url, calendar_id) in calendar.calendar_urls" >
                                    <div class="d-flex flex-row justify-content-between">
                                        <div>
                                        <div>
                                            <span class="dashicons dashicons-yes text-success" ></span> 
                                            Calendar {{ calendar_id}}
                                            <small class="hidden ml-4 text-primary">{{ single_url}}</small>
                                        </div>
                                        <p class="vsmall text-muted m-0 ml-4">
                                        Last checked: <span class="data-item">{{ lastChecked(calendar_id,calendar) }}</span> | 
                                        Last changed: <span class="data-item">{{ lastChanged(calendar_id,calendar) }}</span> | 
                                        Process duration: <span class="data-item">{{ calDuration(calendar_id, calendar) }}</span></p>
                                        </div>
                                        <button  class="align-self-start btn btn-xs btn-link hidden" data-tt="Disconnect Calendar" @click="disconnectCalendar(calendar_id)"><span class="dashicons dashicons-dismiss"></span></button>
                                    </div>                  
                                    
                                </div>
                                <div>
                                    <button class="btn btn-xs btn-outline-primary" @click="goToSync(calendar)" data-tt="Make sure clients can't book you when you're busy">Add calendar</button>
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
            <CalendarsAddEdit :element="elementPassed" @saved="hasBeenSavedDeleted"/>
        </div>
        <div v-if="calendarRegav">
            <button class="btn btn-link btn-xs mb-2" @click="showListing"> < Back</button>
            <WeeklyAvailability :calendar="elementPassed" :timezones_list="elements.timezones_list" :staffs="elements.staffs"/>
        </div>
        <WapModal v-if="showModal" :show="showModal" @hide="hideModal" large noscroll>
            <h4 slot="title" class="modal-title"> 
            <span>Connect Personal calendar</span>
            </h4>

            <Sync @savedSync="savedSync" @errorSaving="errorSavingCalendar" noback></Sync>
        
            <button type="button" class="btn btn-secondary btn-lg mt-2" @click="hideModal">Close</button>
        </WapModal>
    </div>
</template>

<script>

import ServiceCalendar from '../Services/V1/Calendars'
import CalendarsAddEdit from './CalendarsAddEdit'
import WeeklyAvailability from '../RegularAvailability/Edit'
import Connections from '../RegularAvailability/Connections'
import CalUrl from '../Modules/CalUrl'
import Sync from '../Views/Subpages/Sync'
export default {
    extends: window.wappoGet('AbstractListing'),
    components:{
        DurationCell: window.wappoGet('DurationCell'),
        CalendarsAddEdit,
        WeeklyAvailability,
        Connections,
        Sync
    },
    mixins:[CalUrl],
    data: () => ({
        currentView: 'listing',
        viewName:'empty',
        elementPassed: null,
        calendarsOrder: [],
        showModal: false
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
        hideModal(){
            this.showModal = false
        },
        goToSync(calendar) {
            if(calendar.calendar_urls!== false && Object.keys(calendar.calendar_urls).length > 3){
                return
            }
            this.showModal = true
        },
        editAvailability(calendar){
            this.elementPassed = calendar
            this.currentView = 'regav'
        },
        getDayLabel(daykey){
            return daykey
        },
        getUsedDays(regav){
            return Object.keys(regav).filter((e,idx) => idx!== 'precise' && e.length > 0)
            .reduce((obj, key) => {
                if(regav[key].length > 0){
                    obj[key]=regav[key]
                }
                return obj
            }, {});
        },
        convertHours(hours){
            return (hours[0]/60)+'h - '+(hours[1]/60)+'h'
        },
        loadElements() { // overriding
            if(this.currentView == 'listing') {
                this.request(this.requestElements,{},undefined,false,this.loadedElements,this.failedLoadingElements)
            }
        },
        orderChanged(val){
            this.request(this.reorderRequest,{id:val.moved.element.id, 'new_sorting':val.moved.newIndex},undefined,false,this.hasBeenSavedNoReload)
        },
        async reorderRequest(params){
           return await this.mainService.call('reorder',params)
        },
        hasBeenSavedNoReload(result){
            return this.hasBeenSavedDeleted(result, false)
        },
        hasBeenSavedDeleted(result, reload = true){
            if(reload) {
                this.showListing()
                this.loadElements()
            }
            
            if(result.data.message!==undefined)this.$WapModal().notifySuccess(result.data.message)
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
                return this.requiresAddon('services', 'You need to update your Database first')
            }
            if(this.elements.calendars.length > 2){
                return this.requiresAddon('services', '3 services max allowed')
            }

            if(this.crumb){
                this.$emit('updateCrumb',[
                    { target: 'goToMain', label: 'General'},
                    { target: 'goToCalendar', label: 'Calendars', subview: 'listing' },
                    { target: 'goToCalendarAdd', label: 'Add' , disabled:true},
                ], 'add')
            }else{
                this.currentView = 'add'
                this.elementPassed = null
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
.location {
    margin: .2rem;
    color: #717171;
}
.dayLabel {
    text-transform: capitalize;
}
.available-slot{
    background-color: #ccc;
    width: .3rem;
    height: 1rem;
}
.available-slot:hover{
    background-color: #37792a;
}
</style>