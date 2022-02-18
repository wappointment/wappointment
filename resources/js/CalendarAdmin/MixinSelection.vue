
<script>
export default {
    methods:{
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
        let appointment = this.findAppointmentById(eventId)
        if(appointment.extendedProps.recurrent){
          this.cancelRecurringRequest(eventId)
        }else{
          this.cancelRequest(eventId)
        }
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
        for (const element of this.events) {
          if(type === false && element['type']=='appointment') {
            continue //we ignore the appointment events
          }
          if(element['dbid']!== undefined && eventId == element['dbid']) {
            return element
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
      pendingOrderOrNot(params){
        if(params.appointment.extendedProps.options.order_id !== undefined) {
          return `<div class="bg-dark p-2 text-white rounded align-items-center mb-2">
            <div class="h5 text-white"><span class="dashicons dashicons-cart"></span> Pending Order : </div>
            <div class="small text-danger">Awaiting payment for that appointment 
            <a target="_blank" href="/wp-admin/admin.php?page=wappointment_orders">View pending order #${params.appointment.extendedProps.options.order_id}</a></div>
            <div class="text-muted small">The client has ${params.viewData.clean_pending_every} minutes to complete the payment.
            It will then be automatically cancelled.</div>
            <div class="text-white">Click confirm to skip payment for that user.</div>
          </div>`
        }
        return ''
      },
      confirmRequest(eventId){
        let appointment = this.findAppointmentById(eventId)
        let params = {appointment, viewData:this.viewData}

        this.$WapModal().confirm({
          title: 'Do you really want to confirm this appointment?',
          content: window.wappointmentExtends.filter('ConfirmPopup', this.getAppointmentInfoHTML(appointment) +this.pendingOrderOrNot(params), params) 
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
            this.request(this.cancelEventRequest, {id: eventId},undefined,false,  this.refreshEvents)
          } 
        })
      },

      cancelRecurringRequest(eventId){
        this.$WapModal().confirm({
          title: 'Do you really want to cancel this appointment?',
          remember: true,
          rememberLabel:'Delete related appointments too? (child or siblings from a recurrent event)'
        }).then((result) => {
          if(result === false){
              return
          }
          this.request(this.cancelEventRequest, {id: eventId, sibblings: result.remember},undefined,false,  this.cancelEventSuccess)
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
         for (const element of this.events) {
            if(element.id !== undefined && element.id == eventId) {
            return element
          }
         }
      },
      cancelEventSuccess(response){
        this.$WapModal().notifySuccess(response.data['message'])
        if(Array.isArray(response.data['failures']) && response.data['failures'].length > 0){
          this.$WapModal().notifyError(response.data['failures'].join("\n"))
        }
        
        this.refreshEvents()
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

       async cancelEventRequest(params) {
          return await this.serviceEvent.call('delete', params)
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