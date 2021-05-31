
<script>
export default {
    computed: {
      shownAppointmentForm(){
        return this.selectedChoice === 3
      },
      shownBusyConfirm(){
        return this.selectedChoice === 2
      },
      shownFreeConfirm(){
        return this.selectedChoice === 1
      },
      selectionSingleDay(){
        return (this.startTime.day() === this.endTime.day() && 
          this.startTime.month() === this.endTime.month() && 
          this.startTime.year() === this.endTime.year())
      },
      isAvailable(){
        if(this.getThisWeekIntervals!==0) {
          for (let index = 0; index < this.getThisWeekIntervals.intervals.length; index++) {
            const element = this.getThisWeekIntervals.intervals[index]
            if(this.selectIsWithin(element)){
              return true
            }
          }
        }
        return false
      },
      

      isBusy(){
        if(this.getThisWeekIntervals!==0) {
            for (let index = 0; index < this.getThisWeekIntervals.intervals.length; index++) {
              const element = this.getThisWeekIntervals.intervals[index]
              if(
                this.selectWraps(element) ||
                this.selectIntersectsLeft(element) ||
                this.selectIntersectsRight(element) ||
                this.selectIsWithin(element)
              ){
                return false
              }

            }
        }
        
        return true
      },
    },
    methods:{
      selectIsWithin(element){
      let selStart = this.momenttz.tz(this.startTime.format(), this.timezone)
      let selEnd = this.momenttz.tz(this.endTime.format(), this.timezone)
      return selStart.unix() >= element.start 
      && selEnd.unix() <= element.end
    },
    selectWraps(element){
      let selStart = this.momenttz.tz(this.startTime.format(), this.timezone)
      let selEnd = this.momenttz.tz(this.endTime.format(), this.timezone)
      return selStart.unix() <= element.start 
      && selEnd.unix() >= element.end
    },
    selectIntersectsLeft(element){
      return this.startTime.unix() < element.start 
      && this.endTime.unix() > element.start 
      && this.endTime.unix() <= element.end
    },
    selectIntersectsRight(element){
      return this.startTime.unix() >= element.start 
      && this.startTime.unix() < element.end 
      && this.endTime.unix() > element.end
    },
    confirmFree(){
      if(!this.selectionSingleDay || this.isAvailable) return
      if(this.selectionSingleDay){
        this.selectedChoice = 1
      }
    },
    confirmBusy(){
      if(this.isBusy) return
      this.selectedChoice = 2
    },
    confirmNewBooking(){
      if(this.selectionSingleDay){
        this.selectedChoice = 3
      }
    },
     mouseEnter(event){
        //this.viewAppointment(event)
        return false
      },
      mouseLeave(event){
        //this.viewAppointment(event)
        return false
      },
      clickEvent(event){
        this.viewAppointment(event)
        return false
      },

      deleteElement(event){
        let eventId = window.jQuery(event.currentTarget).attr('data-id')
        this.deleteStatus(eventId)
      },
      cancelAppointment(event){
        let eventId = window.jQuery(event.currentTarget).attr('data-id')
        this.cancelRequest(eventId)
      },

      findAppointmentById(eventId){
        let tmp = this.findEventById(eventId, 'appointment')
        return {
          start: tmp.start,
          end:tmp.end,
          extendedProps:tmp,
        }
      },

      findEventById(eventId, type = false){
        for (let index = 0; index < this.events.length; index++) {
          if(type === false && this.events[index]['type']=='appointment') {
            continue //we ignore the appointment events
          }
          if(this.events[index]['dbid']!== undefined && eventId == this.events[index]['dbid']) {
            return this.events[index]
          }
        }
      },

      goToZoom(event){
        let adres = window.jQuery(event.currentTarget).attr('data-href')
        window.open( 
              adres, "_blank"); 
      },
      viewAppointment(event){
        let eventId = window.jQuery(event.currentTarget).attr('data-id')
        let appointment = this.findAppointmentById(eventId)

        this.$WapModalOn({
            title:'Appointment details',
            content: this.getAppointmentInfoHTML(appointment)
        })
      },

      recordDotcom(event){
        let eventId = window.jQuery(event.currentTarget).attr('data-id')
        this.request(this.recordDotcomRequest, eventId, undefined,false,  this.refreshEvents)
      },

      async recordDotcomRequest(eventId) {
          return await this.serviceEvent.call('recordDotcom', {id: eventId})
      },

      confirmAppointment(event){
        let eventId = window.jQuery(event.currentTarget).attr('data-id')
        this.confirmRequest(eventId)
      },

      confirmRequest(eventId){
        let appointment = this.findAppointmentById(eventId)
        this.$WapModal().confirm({
          title: 'Do you really want to confirm this appointment?',
          content: window.wappointmentExtends.filter('ConfirmPopup', this.getAppointmentInfoHTML(appointment), {appointment, viewData:this.viewData}) 
        }).then((result) => {
          if(result === true){
              this.request(this.confirmEventRequest, eventId, undefined,false,  this.refreshEvents)
          } 
        })
      },

      cancelRequest(eventId){
        this.$WapModal().confirm({
          title: 'Do you really want to cancel this appointment?',
        }).then((result) => {
          if(result === true){
            this.request(this.cancelEventRequest, eventId,undefined,false,  this.refreshEvents)
          } 
        })
      },

      deleteStatus(eventId){
        let popupData = {
          title: 'Do you really want to delete this calendar event?',
        }
        let event = this.findEventById(eventId)
        if(event.source!=''){
          popupData['title'] = 'Do you really want to mute this calendar event?'
        }
        if([null,undefined,false].indexOf(event.options)===-1){
          if(event.options.title!==undefined && event.options.title!=''){
            popupData['content'] = `<h3>${event.options.title}</h3>`
          }else{
            if(event.options.conditions!==undefined ){
              popupData['content'] = `<h3>${event.options.conditions}</h3>`
            }
          }
          
        }
        this.$WapModal().confirm(popupData).then((result) => {
          if(result === true){
              this.request(this.deleteStatusRequest, eventId,undefined,false,  this.refreshEvents)
          } 
          
        })

      },

       getEventById(eventId){
        for (let i = 0; i < this.events.length; i++) {
          const element = this.events[i]

          if(element.id !== undefined && element.id == eventId) {
            return element
          }
        }
      },


      selectAllow(selectInfo){
         if(this.isInThePast(selectInfo)) return false
         return true
      },

      eventDragStart(event){
        if(event.editable !== true) return false
        this.disableBgEvent = true
      },

      eventDragStop(event ){
        if(event.editable !== true) return false
        this.disableBgEvent = false
      },

      eventPatch(info ){
        let event = info.event
        let delta =info.delta
        let revertFunc = info.revert
        
        this.$WapModal().confirm({
          title: 'Do you really want to modify this appointment?',
          content: this.getAppointmentInfoHTML(event,delta)
        }).then((result) => {
          if(result === true){
             this.request(this.editEventRequest, {eventId: event.extendedProps.dbid, start: this.toMoment(event.start).unix(), end: this.toMoment(event.end).unix()},undefined,false,  this.refreshEvents)
          }else {
            revertFunc()
          }  
        })

      },

       async cancelEventRequest(eventId) {
          return await this.serviceEvent.call('delete', {id: eventId})
      },
      async confirmEventRequest(eventId) {
          return await this.serviceEvent.call('confirm', {id: eventId})
      },
      async deleteStatusRequest(eventId) {
          return await this.serviceStatus.call('delete', {id: eventId})
      },
      async editEventRequest(params) {
          return await this.serviceEvent.call('patch', {id: params.eventId, start: params.start, end: params.end, timezone: this.displayTimezone})
      },
      
    }
}
</script>