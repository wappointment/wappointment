
<template>
    <div class="saveButtons">
        <span class="wbtn-secondary wbtn googlecal d-flex align-items-center d-flex-inline" role="button" @click="goToUrl(saveToGoogle)">
            <WapImage :faIcon="['fab', 'google']" size="md" /> <span class="ml-2">Google</span>
        </span>
        <a class="wbtn-secondary wbtn outlook d-flex align-items-center d-flex-inline" :href="saveToIcal" download="event.ics">
            <WapImage :faIcon="['fab', 'windows']" size="md" /> <span class="ml-2">Outlook</span>
        </a>
        <span class="wbtn-secondary wbtn outlook d-flex align-items-center d-flex-inline" role="button" @click="goToUrl(saveToOutlookOnline)">
            <WapImage :faIcon="['fab', 'windows']" size="md" /> <span class="ml-2">Outlook Live</span>
        </span>
        <a class="wbtn-secondary wbtn d-flex align-items-center d-flex-inline" :href="saveToIcal" download="event.ics">
            <WapImage :faIcon="['fab', 'apple']" size="md" /> <span class="ml-2">iCal</span>
        </a>
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
    props: ['service', 'staff', 'currentTz', 'physicalSelected','appointment', 'showResult', 'options', 'ticket'],

    methods: {
        goToUrl(url){
            window.open(url)
        },

        toIsoString(date){
            return date.toISOString().replace(/-|:|\.\d+/g, '');
        },

        getTitle(title, html = false){
            return (html?'<strong>':'')+title+(html?'</strong>':'')
        },
         
        getAppointmentDetails(html = false){
            switch (this.appointment.type) {
                case 'phone':
                    return this.getTitle(this.options.i18n.a_is_phone, html)+
                    lnb + this.options.i18n.a_with_phone.replace('%s',this.getClientPhone)
                case 'zoom':
                    return this.getTitle(this.options.i18n.a_is_video, html)+
                    lnb + this.options.i18n.a_with_videolink +
                    lnb + this.generateLink('view-event', html, this.options.i18n.begin_meeting)
                case 'skype':
                    return this.getTitle(this.options.i18n.a_is_skype, html)+
                    lnb + this.options.i18n.a_with_phone.replace('%s',this.getSkypeUsername) 
                case 'physical':
                    return lnb + this.getTitle(this.options.i18n.a_is_address, html)+ 
                    lnb + this.eventLocation
            }
        },

        eventDescription(html = false){
             let pwd_link = !html ? apiWappointment.signature:apiWappointment.signature.replace('https://wappointment.com','<a href="https://wappointment.com?utm_source=plugin&utm_medium=ics&utm_campaign=appointment">Wappointment</a>')
             return this.getAppointmentDetails(html) + this.getLinks(html) + lnb + this.getLineSeparator('_', pwd_link.length/2) +
             lnb + lnb + pwd_link
        },

        getLineSeparator(char = '-', length = 30){
            let string = ''
            while (string.length < length+1){
                string += char
            }
            return string
        },
        
        generateLink(typeLink = 'view-event', html = false, label = ''){
            let url = apiWappointment.frontPage + '&view='+typeLink+'&appointmentkey=' + this.appointment.edit_key
            return this.getLink(window.wappointmentExtends.filter('urlAppointmentKey', url, {appointment:this.appointment, ticket:this.ticket})
            , html , label )
        },

        getLink(url, html = false, label = ''){
            return (html ? "<a href='"+url+"' target='_blank'>":'')+ (html && label!= '' ?label:url) + (html ? "</a>":'')
        },
         getLinks(html = false){
            if(html){
                return lnb + lnb +  (this.canReschedule? this.generateLink('reschedule-event', html, this.options.i18n.reschedule):'') + 
                ((this.canCancel && this.canReschedule) ?' - ':'') 
                + (this.canCancel ? this.generateLink('cancel-event', html, this.options.i18n.cancel):'')
            }
            return (this.canReschedule? lnb + lnb + this.options.i18n.reschedule+" " + lnb +  this.generateLink('reschedule-event'):'') +
            (this.canCancel ? lnb + lnb + this.options.i18n.cancel+" " + lnb + this.generateLink('cancel-event'):'')
        },
        getUnixNow(){
            return momenttz().unix()
        },
        getPhysicalAddress(){
            let location_id = this.appointment.location_id
            let found = this.service.locations.find(e => e.id == location_id)
            return found !== undefined ? found.options.address:this.appointment.location_label
        },
    },
    computed: {
        canCancel(){
            return this.appointment.can_cancel_until !== undefined && this.getUnixNow() < this.appointment.can_cancel_until
        },
        canReschedule(){
            return this.appointment.can_reschedule_until!== undefined && this.getUnixNow() < this.appointment.can_reschedule_until
        },

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
            return momenttz.unix(this.endDateMinusBuffer)
        },

        endDateMinusBuffer(){
            let buffer_time_sec = this.appointment.options.buffer_time !== undefined ? parseInt(this.appointment.options.buffer_time) *60:0
            return this.appointment.end_at - buffer_time_sec
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

        getSkypeUsername(){
            return this.appointment.client.options !== undefined && this.appointment.client.options.skype !==undefined ?  this.appointment.client.options.skype:''
        },
        getClientPhone(){
            return this.appointment.client.options !== undefined && this.appointment.client.options.phone !==undefined ?  this.appointment.client.options.phone:''
        },

        encodedEventTZ(){
            return encodeURIComponent(this.currentTz)
        },

        eventLocation(){
            return this.appointment.type == 'physical' ? this.getPhysicalAddress():this.appointment.location_label
        },
        
        saveToIcal() {

            return encodeURI(
                'data:text/calendar;charset=utf8,' +
                [
                'BEGIN:VCALENDAR',
                'VERSION:2.0',
                'BEGIN:VEVENT',
                'ORGANIZER:'          + this.appointment.ics_organizer,
                'CATEGORIES:APPOINTMENT'+
                'TRANSP:OPAQUE'+
                'URL:'          + document.URL,
                'DTSTART:'      + this.formattedStartDate,
                'DTEND:'        + this.formattedEndDate,
                'SUMMARY:'      + this.eventTitle,
                'DESCRIPTION:'  + this.eventDescription(),
                'LOCATION:'     + this.eventLocation,
                'END:VEVENT',
                'END:VCALENDAR'
                ].join('\n')
            );
        },

        saveToYahoo() {
            let url = 'https://calendar.yahoo.com/?v=60&view=d&type=20'
            url += '&title=' + this.encodedEventTitle
            url += '&st=' + this.formattedStartDate 
            url += '&et=' + this.formattedEndDate
            url += '&in_loc=' + encodeURIComponent(this.eventLocation.replaceAll('\n', ', '))
            url += '&desc=' + encodeURIComponent(this.eventDescription().replaceAll("\\n", "\n"))
            return url
        },    
    
        saveToGoogle(){
            let url = 'http://www.google.com/calendar/event?action=TEMPLATE'
            url += '&text=' + this.encodedEventTitle;
            url += '&dates=' + this.formattedStartDate + '/' + this.formattedEndDate
            url += '&ctz=' + this.encodedEventTZ
            url += '&trp=true'
            url += '&sprop=' + apiWappointment.apiSite
            url += '&details=' + encodeURIComponent(this.eventDescription(true).replaceAll("\\n", '<br/>'))
            url += '&location=' + encodeURIComponent(this.eventLocation.replaceAll('\n', ', '))
            return url
        },

        saveToOutlookOnline() {

            let url = 'https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent'
            url += '&subject=' + encodeURIComponent(this.eventTitle.replaceAll(' ',"\n"))
            url += '&startdt=' + this.formattedStartDateMS 
            url += '&enddt=' + this.formattedEndDateMS
            url += '&location=' + encodeURIComponent(this.eventLocation.replaceAll('\n', ', ').replaceAll(' ',"\n"))
            url += '&body=' + encodeURIComponent(this.eventDescription(true).replaceAll("\\n", '<br/>').replaceAll(' ',"\n"))
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

