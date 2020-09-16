
<template>
    <div class="saveButtons">
        <span class="wbtn-secondary wbtn googlecal d-flex align-items-center d-flex-inline" role="button" @click="goToUrl(saveToGoogle)">
            <WapImage :faIcon="['fab', 'google']" size="md" /> <span class="ml-2">Google</span>
        </span>
        <span class="wbtn-secondary wbtn outlook d-flex align-items-center d-flex-inline" role="button" @click="goToUrl(saveToIcal)">
            <WapImage :faIcon="['fab', 'windows']" size="md" /> <span class="ml-2">Outlook</span>
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
        encodedEventTitle(){
            return encodeURIComponent(this.eventTitle)
        },
        eventTitle(){
            return this.service.name + ' - ' + this.staff.n
        },
         eventDescription(){
            return ''
        },
        encodedEventDescription(){
            return '' 
        },
        encodedEventTZ(){
            return encodeURIComponent(this.currentTz)
        },
        eventLocation(){
            return this.service.address
        },
        encodedEventLocation(){
            return encodeURIComponent(this.eventLocation)
        },

        saveToGoogle(){
            let url = 'http://www.google.com/calendar/event?action=TEMPLATE'
            url += '&text=' + this.encodedEventTitle;
            url += '&dates=' + this.formattedStartDate + '/' + this.formattedEndDate;
            url += '&ctz=' + this.encodedEventTZ
            if (this.physicalSelected) {
                url += '&location=' + this.encodedEventLocation;
            }
            return url

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

  
        saveToOutlookOnline() {

            let url = 'http://calendar.live.com/calendar/calendar.aspx?rru=addevent&'
            url += 'summary=' + this.encodedEventTitle
            url += '&dtstart=' + this.formattedStartDate.slice(0, -1) 
            url += '&dtend=' + this.formattedEndDate.slice(0, -1)
            //url += '&ctz=' + this.encodedEventTZ
            if (this.physicalSelected) {
                url += '&location=' + this.encodedEventLocation
            }
            url += '&description=' + this.encodedEventDescription
            return url

        },

        saveToYahoo() {

            let url = 'https://calendar.yahoo.com/?v=60&view=d&type=20'
            url += '&title=' + this.encodedEventTitle;
            url += '&st=' + this.formattedStartDate 
            url += '&et=' + this.formattedEndDate;
            //url += '&ctz=' + this.encodedEventTZ
            if (this.physicalSelected) {
                url += '&in_loc=' + this.encodedEventLocation;
            }
            url += '&descdescription=' + this.encodedEventDescription;
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

