
<script>
export default {

    methods:{
        eventRender(renderInfo){
        
        let event = renderInfo.event
        let eventExt = event.extendedProps

        
        let element = window.jQuery(renderInfo.el)
        this.isParentInThePast(element)
         //'.fc-content-skeleton tr td'

        element.attr('data-id', eventExt.dbid)
        element.attr('id', 'event-'+eventExt.type+event.id)
        element.attr('data-rendering', event.rendering)

        
        if(eventExt.onlyDelete!==undefined){
          element.attr('data-only-delete', 1)
        }
        if(eventExt.past !== undefined && eventExt.past === true) {
          element.addClass('past-event')
          
          if(this.isAppointmentEvent(event.rendering)){
              element.find('.fc-time').html(this.getAppointmentHtml(event,element))
              element.append('<div class="fc-bg"></div>'+this.getBufferHtml(event))
              element.click(this.cancelClick)
              element.mouseenter(this.EOver)
              element.mouseleave(this.EOut)
            }
        }else{

          if(event.rendering =='background'){
            element.mousedown(this.bgEOut)
            if([undefined,null].indexOf(eventExt.options)  === -1  && eventExt.options.title !== undefined){
              element.attr('data-calendar-title', eventExt.options.title)
            }
            
            element.click(this.cancelClick)
            element.mouseenter(this.bgEOverDelay)
            element.mouseleave(this.bgEOut)
          }else{
            if(this.isAppointmentEvent(event.rendering)){
              element.find('.fc-time').html(this.getAppointmentHtml(event,element))
              element.append('<div class="fc-bg"></div>'+this.getBufferHtml(event))
              element.click(this.cancelClick)
              element.mouseenter(this.EOver)
              element.mouseleave(this.EOut)
            }
          }
        }

      },

      EOver(event){
        if(this.disableBgEvent) {
          return false
        }

        this.disableSelectClick = true
        
        this.attachEvent( window.jQuery(event.currentTarget))
        
      },

      EOut(event){
        
        this.disableSelectClick = false

        if(this.disableBgEvent) {
          return false
        }

        /*window.jQuery(event.target).parent().css('z-index',2)*/
        let el = window.jQuery(event.currentTarget)
        el.removeClass('hover')
        el.find('.fill-event').remove()

      },

      bgEDown(event){ 
        this.hasBeenClicked = window.jQuery(event.currentTarget).attr('data-id')
        event.stopPropagation()
      },

      modifyRegav(event){
        //this.$router.push({ name: 'regav'})
        this.showRegularAv = true
        event.stopPropagation()
      },

      isAppointmentEvent(datarendering){
        return ['appointment-confirmed', 'appointment-pending'].indexOf(datarendering) !== -1
      },
      isAppointmentPending(datarendering){
        return 'appointment-pending' == datarendering
      },
      isAppointmentConfirmed(datarendering){
        return 'appointment-confirmed' == datarendering
      },
      attachEvent(el) {
        el.addClass('hover')

        if(el.attr('data-only-delete')!==undefined){
          let innerhtml ='<div class="fill-event">'

          if(el.attr('data-rendering')!== undefined && el.attr('data-rendering')=='background'){
            if(el.hasClass('extra')) {
              innerhtml += '<div class="crib blue">Punctual Availability</div>'
            }else{
              if(el.hasClass('busy')){
                innerhtml += '<div class="crib red">Busy</div>'
              }else{
                if(el.hasClass('recurrent')){
                  innerhtml += '<div class="crib orange">Personal Calendar recurs <span class="dashicons dashicons-update"></span></div>'
                }else{
                  innerhtml += '<div class="crib orange">Personal Calendar</div>'
                }
                if(el.attr('data-calendar-title')!== undefined){
                    if(el.children('.cal-title').length == 0){
                      innerhtml += '<div class="cal-title">'+el.attr('data-calendar-title')+'</div>'
                    }
                }
              }
              
            }
          }else{
            innerhtml += '<div class="crib yel">Appointment</div>'
          }
          
          if(!el.hasClass('past-event') || (el.hasClass('past-event') && this.isAppointmentEvent(el.attr('data-rendering')))){
            innerhtml += '<div class="d-flex justify-content-center align-items-center mx-4 ctrlbar">'
            if(this.isAppointmentConfirmed(el.attr('data-rendering'))) innerhtml += '<button class="btn btn-xs btn-light viewElement" data-id="'+el.attr('data-id')+'"><span class="dashicons dashicons-visibility"></span></button>'
            if(this.isAppointmentPending(el.attr('data-rendering'))) innerhtml += '<button class="btn btn-xs btn-light confirmElement" data-id="'+el.attr('data-id')+'"><span class="dashicons dashicons-yes"></span></button>'
            
            if(!el.hasClass('past-event')){
              if(this.isAppointmentEvent(el.attr('data-rendering'))) {
                innerhtml += '<button class="btn btn-xs btn-light cancelAppointment" data-id="'+el.attr('data-id')+'"><span class="dashicons dashicons-dismiss"></span></button>'
              }else{
                let spanIcon = el.hasClass('calendar') ? '<span class="dashicons dashicons-controls-volumeoff"></span> ': '<span class="dashicons dashicons-trash"></span>'
                innerhtml += '<button class="btn btn-xs btn-light deleteElement" data-id="'+el.attr('data-id')+'">'+spanIcon+'</button>'
              }
                
            }
            
            innerhtml += '</div>'
          }
          innerhtml += '</div>'// endfill-event
          

          if(this.isAppointmentEvent(el.attr('data-rendering'))){
            //el.find('.fc-content').append(innerhtml)
            el.find('.fc-bg').append(innerhtml)
            el.find('.cancelAppointment').on( "click", this.cancelAppointment)
            el.find('.viewElement').on( "click", this.viewAppointment)
            el.find('.confirmElement').on( "click", this.confirmAppointment)
          } else{
            el.append(innerhtml)
            el.find('.deleteElement').on( "click", this.deleteElement)
          }
          
        }else{
          if(el.hasClass('opening')){
            let innerhtml = '<div class="fill-event"><div class="crib grey">Weekly Availability</div>'
            innerhtml += '<div class="d-flex justify-content-center align-items-center mx-4 ctrlbar"><button class="btn btn-xs btn-light modifyRegav" data-id="'+el.attr('data-id')+'"><span class="dashicons dashicons-edit"></span></button></div>'
            innerhtml += '</div>' // endfill-event
            el.append(innerhtml)
            el.find('.modifyRegav').on( "click", this.modifyRegav)
          }
          
        }
        let heightTarget = el.height()

        if(el.find('.ctrlbar').length > 0) {
          let marginTopControls = Math.floor((heightTarget - el.find('.ctrlbar')[0].offsetHeight)/2)

          if(marginTopControls > 0 ){
            el.find('.ctrlbar').css('margin-top', marginTopControls+'px')
          }
        }
        
      },
      bgEOverDelay(event){
        this.cancelbgOver = setTimeout(this.bgEOver.bind('',event),100)
      },
      
      bgEOver(event,fsdfsd){
        let el =  window.jQuery(event.target)
        //return;
        if(!el.hasClass('fc-bgevent')) {
          return
        }
        this.parentAttach = el.parent()

        if(this.disableBgEvent) {
          return false
        }

        if(this.parentAttach.parent('.fc-content-col').find('.fc-highlight-container .fc-highlight').length>0) {
          this.disableBgEvent = true
          return false
        }
        this.activeBgOverId = el.attr('data-id')
        window.jQuery('[data-id="' + this.activeBgOverId + '"]').addClass('hover')

        this.disableSelectClick = true


        this.attachEvent(el)
        
        el.css('z-index', 9)

        el.attr('data-parent-class', this.parentAttach.attr('class') )
        el.insertAfter(this.parentAttach.parent('.fc-content-col').find('.fc-highlight-container'))
        

      },

      bgEOut(event){
        window.jQuery('[data-id="' + this.activeBgOverId + '"]').removeClass('hover')
        this.activeBgOverId = false
        
        if(this.cancelbgOver) {
          clearTimeout(this.cancelbgOver)
          this.cancelbgOver = false
        }
        if(event.type=='mousedown' && window.jQuery(event.target).hasClass('btn-secondary')) {
          this.$refs.calendar.fireMethod('unselect')
          return false
        }
        this.disableSelectClick = false

        if(this.disableBgEvent) {
          return false
        }

        let el = window.jQuery(event.target)
        el.innterHtml = ''
        if(el.attr('data-parent-class') !== undefined){
          this.reAttach(el)
        }else{
          
          let newEl = el.parent()
          if(newEl.attr('class').search("fc-bgevent")!==-1){
            this.reAttach(newEl)
          }else{
            this.reAttach(newEl.parent())
          }
        }
        this.parentAttach = undefined

      },

      reAttach(el){
          el.appendTo(el.parent().find('.'+el.attr('data-parent-class')))
        
          el.removeClass('hover')
          el.find('.fill-event').remove()
          el.css('z-index', '')
          el.removeAttr('data-parent-class')
      },
      
      cancelClick(event){
          event.stopPropagation()
          return false
      },
      bgEClick(event){
        let eventId = window.jQuery(event.currentTarget).attr('data-id')

      },
    }
}
</script>