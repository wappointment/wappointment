<template>
    <div >
        <div v-if="calendarListing">
            <button @click="showCalendar" class="btn btn-outline-primary btn my-2">Add new</button>
            <div class="table-responsive table-hover">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Weekly Availability</th>
                            <th scope="col">Services</th>
                            <th scope="col">Connections</th>
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
    </div>
</template>

<script>

import ServiceCalendar from '../Services/V1/Calendars'
import CalendarsAddEdit from './CalendarsAddEdit'
import WeeklyAvailability from '../RegularAvailability/Edit'
import Connections from '../RegularAvailability/Connections'
export default {
    extends: window.wappoGet('AbstractListing'),
    components:{
        DurationCell: window.wappoGet('DurationCell'),
        CalendarsAddEdit,
        WeeklyAvailability,
        Connections
    },
    data: () => ({
        currentView: 'listing',
        viewName:'empty',
        elementPassed: null,
        calendarsOrder: []
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