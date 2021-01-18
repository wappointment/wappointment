
<template>
    <div class="saveButtons">
        <span class="wbtn-secondary wbtn googlecal d-flex align-items-center d-flex-inline" role="button" @click="goToUrl(saveToGoogle)">
            <WapImage :faIcon="['fab', 'google']" size="md" /> <span class="ml-2">Google</span>
        </span>
        <span class="wbtn-secondary wbtn outlook d-flex align-items-center d-flex-inline" role="button" @click="goToUrl(saveToIcal)">
            <WapImage :faIcon="['fab', 'windows']" size="md" /> <span class="ml-2">Outlook</span>
        </span>
        <span class="wbtn-secondary wbtn outlook d-flex align-items-center d-flex-inline" role="button" @click="goToUrl(saveToOutlookOnline)">
            <WapImage :faIcon="['fab', 'windows']" size="md" /> <span class="ml-2">Outlook Live</span>
        </span>
        <span class="wbtn-secondary wbtn d-flex align-items-center d-flex-inline" role="button" @click="goToUrl(saveToIcal)">
            <WapImage :faIcon="['fab', 'apple']" size="md" /> <span class="ml-2">iCal</span>
        </span>
        <span class="wbtn-secondary wbtn d-flex align-items-center d-flex-inline" role="button" @click="goToUrl(saveToYahoo)">
            <WapImage :faIcon="['fab', 'yahoo']" size="md" /> <span class="ml-2">Yahoo</span>
        </span>
    </div>
</template>

<script>

import momenttz from '../appMoment'
import convertDateFormatPHPtoMoment from '../Standalone/convertDateFormatPHPtoMoment'
const lnb = "\\n"
export default {
    props: ['service', 'staff', 'currentTz', 'physicalSelected','appointment', 'showResult'],

    methods: {
        goToUrl(url){
            window.open(url)
        },

        toIsoString(date){
            return date.toISOString().replace(/-|:|\.\d+/g, '');
        },
         
    },
    computed: {
        isoFormat(){
            return convertDateFormatPHPtoMoment('Ymd') + '[T]' + convertDateFormatPHPtoMoment('His')
        },

        startDate(){
            return momenttz.unix(this.appointment.start_at)
        },

        getDuration(){
            return this.service.duration
        },

        endDate(){
            return momenttz.unix(this.appointment.end_at)
        },

        formattedStartDate(){
            return this.startDate.format(this.isoFormat)
        },
        
        formattedEndDate(){
            return this.endDate.format(this.isoFormat)
        },

        formattedStartDateMS(){
            return this.startDate.toISOString()
        },
        
        formattedEndDateMS(){
            return this.endDate.toISOString()
        },

        encodedEventTitle(){
            return encodeURIComponent(this.eventTitle)
        },

        eventTitle(){
            return this.service.name + ' - ' + this.staff.n
        },

        getAppointmentDetails(){
            switch (this.appointment.type) {
                case 'phone':
                    return 'Appointment over the phone'
                case 'zoom':
                    let link_view = apiWappointment.frontPage + '&view=view-event&appointmentkey=' + this.appointment.edit_key
                    return 'Appointment is a Video meeting'+
                    lnb + "Meeting will be accessible from the link below:" +
                    lnb + "<a href='"+link_view+"' target='_blank'>"+ link_view + '</a>'
                case 'skype':
                    return 'Appointment on Skype '+
                    lnb + "We will call you on " + this.getSkypeUsername
                case 'physical':
                    return lnb + 'Appointment at this address'+ 
                    lnb + this.eventLocation
            }
        },

        getLinks(){
            return lnb + lnb + "Reschedule: " + lnb +  apiWappointment.frontPage + '&view=reschedule-event&appointmentkey=' + this.appointment.edit_key+
            lnb + lnb + "Cancel: " + lnb + apiWappointment.frontPage + '&view=cancel-event&appointmentkey=' + this.appointment.edit_key
        },

        getSkypeUsername(){
            return this.appointment.client.options !== undefined && this.appointment.client.options.skype !==undefined ?  this.appointment.client.options.skype:''
        },

        eventDescription(){
             return this.getAppointmentDetails + this.getLinks + lnb + "-----------------------------------" +
             lnb + "Booked with " + apiWappointment.apiSite
        },
        encodedEventDescription(){
            return encodeURIComponent(this.eventDescription.replaceAll("\\n", '<br/>'))
        },

        encodedEventTZ(){
            return encodeURIComponent(this.currentTz)
        },

        eventLocation(){
            return this.physicalSelected ? this.service.address:this.appointment.type
        },

        encodedEventLocation(){
            return encodeURIComponent(this.eventLocation.replaceAll("\\n", '<br/>'))
        },
        
         saveToIcal() {

            return encodeURI(
                'data:text/calendar;charset=utf8,' +
                [
                'BEGIN:VCALENDAR',
                'VERSION:2.0',
                'BEGIN:VEVENT',
                'URL:'          + document.URL,
                'DTSTART:'      + this.formattedStartDate,
                'DTEND:'        + this.formattedEndDate,
                'SUMMARY:'      + this.eventTitle,
                'DESCRIPTION:'  + this.eventDescription,
                'LOCATION:'     + this.eventLocation,
                'END:VEVENT',
                'END:VCALENDAR'
                ].join('\n')
            );
        },


        saveToGoogle(){
            let url = 'http://www.google.com/calendar/event?action=TEMPLATE'
            url += '&text=' + this.encodedEventTitle;
            url += '&dates=' + this.formattedStartDate + '/' + this.formattedEndDate
            url += '&ctz=' + this.encodedEventTZ
            url += '&trp=true'
            url += '&sprop=' + apiWappointment.apiSite
            url += '&details=' + this.encodedEventDescription
            url += '&location=' + this.encodedEventLocation
            
            return url
        },

        saveToOutlookOnline() {

            let url = 'https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent'
            url += '&subject=' + encodeURIComponent(this.eventTitle.replaceAll(' ',"\n"))
            url += '&startdt=' + this.formattedStartDateMS 
            url += '&enddt=' + this.formattedEndDateMS
            url += '&location=' + encodeURIComponent(this.eventLocation.replaceAll("\\n", '<br/>').replaceAll(' ',"\n"))
            url += '&body=' + encodeURIComponent(this.eventDescription.replaceAll("\\n", '<br/>').replaceAll(' ',"\n"))
            return url

        },

        saveToYahoo() {

            let url = 'https://calendar.yahoo.com/?v=60&view=d&type=20'
            url += '&title=' + this.encodedEventTitle
            url += '&st=' + this.formattedStartDate 
            url += '&et=' + this.formattedEndDate
            url += '&in_loc=' + encodeURIComponent(this.eventLocation.replaceAll("\\n", "\n"))
            url += '&desc=' + encodeURIComponent(this.eventDescription.replaceAll("\\n", "\n"))
            return url

        },
    }
}
</script>
<style>
.saveButtons .wbtn-secondary{
    margin: .2em 0 !important;
}
</style>

