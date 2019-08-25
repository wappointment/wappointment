<script>
import momenttz from '../appMoment'
import Helpers from '../Standalone/helpers'
momenttz.tz.setDefault()
export default {
    props: ['selectedSlot', 'service', 'staff', 'currentTz', 'physicalSelected'],
    methods: {
        toIsoString(date){
            return date.toISOString().replace(/-|:|\.\d+/g, '');
        },
    },
    computed: {
        isoFormat(){
            return (new Helpers()).convertPHPToMomentFormat('Ymd') + '[T]' + (new Helpers()).convertPHPToMomentFormat('His')
        },
        startDate(){
            return momenttz.unix(this.selectedSlot)
        },
        endDate(){
            return momenttz.unix(this.selectedSlot + (this.service.duration * 60))
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
            return '' //encodeURIComponent(this.service.description)
        },
        encodedEventTZ(){
            return encodeURIComponent(this.currentTz)
        },
        eventLocation(){
            return this.service.address
        },
        encodedEventLocation(){
            return encodeURIComponent(this.service.address)
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
        saveToOutlook(){
            /* BEGIN:VCALENDAR
            VERSION:2.0
            BEGIN:VEVENT
            DTSTAMP:20190207T123608Z
            STATUS:CONFIRMED
            UID:1549542968addeventcom
            SEQUENCE:0
            DTSTART:20190221T160000Z
            DTEND:20190221T180000Z
            SUMMARY:Summary of the event
            DESCRIPTION:Description of the event
            X-ALT-DESC;FMTTYPE=text/html:Description of the event
            LOCATION:Location of the event
            BEGIN:VALARM
            TRIGGER:-PT15M
            ACTION:DISPLAY
            END:VALARM
            TRANSP:OPAQUE
            END:VEVENT
            END:VCALENDAR

            BEGIN:VCALENDAR
            VERSION:2.0
            BEGIN:VEVENT
            DTSTAMP:20190207T123249Z
            STATUS:CONFIRMED
            UID:1549542769addeventcom
            SEQUENCE:0
            DTSTART:20190221T160000Z
            DTEND:20190221T180000Z
            SUMMARY:Summary of the event
            DESCRIPTION:Description of the event
            X-ALT-DESC;FMTTYPE=text/html:Description of the event
            LOCATION:Location of the event
            BEGIN:VALARM
            TRIGGER:-PT15M
            ACTION:DISPLAY
            END:VALARM
            TRANSP:OPAQUE
            END:VEVENT
            END:VCALENDAR */
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
            /*var startDate = new Date(this.eventData.start),
                endDate = new Date(this.eventData.end);

             startDateTimezoneOffset = startDate.getTimezoneOffset();
            startDate.setMinutes(startDate.getMinutes() - 2 * startDateTimezoneOffset); // HACK

            endDateTimezoneOffset = endDate.getTimezoneOffset();
            endDate.setMinutes(endDate.getMinutes() - endDateTimezoneOffset); // HACK

            startDate = this.formatTime(startDate).slice(0, -1);
            endDate =  this.formatTime(endDate).slice(0, -1); */

/*             let outlookOnlineArgs = {
                'summary'     : this.eventTitle,
                'dtstart'     : this.formattedStartDate,
                'dtend'       : this.formattedEndDate,
                'location'    : this.encodedEventLocation,
                'description' : this.encodedEventDescription
            };  */

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
            //var startDate = this.formatTime(new Date(this.eventData.start));

            // FIXED: Yahoo! calendar bug
            // 
            // Yahoo! did calculate timezone for `start`
            // but they did not calculate timezone for `end`
            /* var tmp = new Date(this.selectedSlot);
            var timezoneOffset = tmp.getTimezoneOffset();
            tmp.setMinutes(tmp.getMinutes() - timezoneOffset);
            var endDate = this.formatTime(tmp);
            this.toIsoString()  */
           /*  var yahooArgs = {
                'title'     : this.encodedEventTitle,
                'st'        : this.formattedStartDate,
                'et'        : this.formattedEndDate,
                // 'dur'       : '',
                'in_loc'    : this.encodedEventLocation,
                'desc'      : this.encodedEventDescription
            };
 */
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
