<script>
import CanFormatPrice from '../Mixins/CanFormatPrice'
export default {
  mixins: [CanFormatPrice],
    methods:{
      getClientAppointment(event){
        return`<div>
                ${this.getAppointmentPicture(event, true)}
                ${this.longDescription(event)}
                </div>`

      },
      getFieldLabel(namekey){
           if(namekey == 'tz') {
             return 'Timezone'
           }
           if(namekey == 'owes') {
             return 'Owes'
           }
            for (let i = 0; i < this.viewData.custom_fields.length; i++) {
                const element = this.viewData.custom_fields[i]
                if(element['namekey'] == namekey) return element.name
            }
        },
      getValueLabel(namekey, values){
          let newvalues = values
            for (let i = 0; i < this.viewData.custom_fields.length; i++) {
                const element = this.viewData.custom_fields[i]
                if(element['namekey'] == namekey && element.values !== undefined){
                  if(Array.isArray(values)){

                    for (let k = 0; k < values.length; k++) {
                      const valuekey = values[k]
                      newvalues[k]= this.getValueLabelFrom(element.values, valuekey)
                    }
                    newvalues = newvalues.join(', ')
                  }else{
                    newvalues = this.getValueLabelFrom(element.values, newvalues)
                  }
                  
                }
            }
            return this.cleanString(newvalues)
        },

        getValueLabelFrom(valuesDescriptions, keyValue){
          for (let j = 0; j < valuesDescriptions.length; j++) {
              const subelement = valuesDescriptions[j]

              if(subelement.value == keyValue){
                return this.cleanString(subelement.label)
              }  
            }
            return 'errorLabel'
        },
        getAppointmentInfoHTML(appointment, delta = false){
            return (delta !== false) ? this.getOldAndNewAppointment(appointment, delta):this.getScheduledTime(appointment)
        },

        getScheduledTime(appointment){
          let start = this.toMoment(appointment.start)
          let end = this.toMoment(appointment.end)
            return `
                <div class="d-sm-flex justify-content-around align-items-center my-2">
                ${this.getClientAppointment(appointment)}
                
                <div class="bg-light border border-primary rounded p-2 text-center">
                    <div> Scheduled Time </div>
                    ${this.getLocation(appointment)} 
                    ${this.getAppointmentTimeAndDate(start, end)}
                </div>
                </div>
            `
        },

      
        getOldAndNewAppointment(appointment, delta){

            let dms = -delta.milliseconds
            let daysdelta = -delta.days
            
            let oldStart = this.toMoment(appointment.start).clone().add(dms, 'ms').add(daysdelta, 'd')
            let oldEnd = this.toMoment(appointment.end).clone().add(dms, 'ms').add(daysdelta, 'd')
            return `
                <div class="d-flex justify-content-center">${this.getClientAppointment(appointment, 'm-auto')}<hr></div>
                <div class="d-sm-flex justify-content-around align-items-center my-2">
                    <div class="bg-light rounded p-2">
                    <div> Old schedule </div>
                    ${this.getAppointmentTimeAndDate(oldStart, oldEnd, 'text-danger')}
                    </div>
                    <div class="d-sm-block d-none"> <span class="dashicons dashicons-arrow-right-alt2"></span> </div>
                    <div class="d-sm-none"> <span class="dashicons dashicons-arrow-down-alt2"></span> </div>
                    <div class="bg-light border border-primary rounded p-2">
                    <div> New schedule </div>
                    ${this.getAppointmentTimeAndDate(this.toMoment(appointment.start), this.toMoment(appointment.end), 'text-success')}
                    </div>  
                </div>
            `
        },

        getAppointmentTimeAndDate(momentStart, momentEnd, className=''){
            return `<div>
                    <div class="${className}"> 
                    ${ this.formatTime(momentStart, this.viewData.date_format ) } 
                    </div>
                    <div class="${className}">
                    From ${this.formatTime(momentStart)} until ${this.formatTime(momentEnd)} 
                    </div>
                    <div class="small font-italic">${this.displayTimezone}</div>
                </div>`
        },
      getBuffer(event){
        return [undefined,null].indexOf(event.extendedProps)===-1  && 
        [undefined,null].indexOf(event.extendedProps.options)===-1 && 
        event.extendedProps.options.buffer_time !== undefined ? event.extendedProps.options.buffer_time:0
      },
      getBufferHtml(event){
        return this.getBuffer(event) > 0 ?`<div class="buffer">Buffer: ${this.getBuffer(event)}min</div>`:''
      },

      getClientAvatarSize(url, size = 30, float = false){
        return `<img class="${this.getClassAvatar(float)}" src="${url.replace('s=30', 's='+size)}">`
      },
      getClassAvatar(float){
        return 'rounded-circle img-fluid img-max ' + (float ? ' float-left mr-2 mt-2':'')
      },
      getAppointmentPicture(event, float = false){
        return  this.getClientAvatarSize(event.extendedProps.client === null ?event.extendedProps.wimage:event.extendedProps.client.avatar, 30, float)
      },
      getShortAppointmentHtml(event){
        return this.$sanitize(`<div class="d-flex">
                  <div> ${this.getAppointmentPicture(event)} </div>
                  <div class="ml-1">
                    ${ this.shortDescription(event)}
                  </div>
                </div>`)
      },
      longDescription(event){
        return this.shortDescription(event) + this.loopThroughItems(event.extendedProps.display.long)
      },
      shortDescription(event){
        return this.loopThroughItems(event.extendedProps.display.short)
      },
      loopThroughItems(object){
        return Object.values(object).map(line => `<div>${line}</div>`).join('')
      },
      getLocation(event){
        return `<div>${this.getIconClass(event)} ${this.getLocationName(event)} </div>`
      },
      getService(event){
        let duration = (this.toMoment(event.end).unix()-this.toMoment(event.start).unix()- (this.getBuffer(event)*60))/60
        return `<div>${this.getIconClassService(event)} ${this.getServiceName(event)} : ${duration}min </div>`
      },
      getServiceName(event){
        return this.isAppointmentCoreSystem(event) ? this.viewData.service.name: ( event.extendedProps.service !== undefined ? this.cleanString(event.extendedProps.service.name):'')
      },

      getLocationName(event){
        return this.isAppointmentCoreSystem(event) ? this.cleanString(event.extendedProps.location):this.getLocationAttribute(event,'name')
      },
      getIconClassService(event){
        return  event.extendedProps.service !== undefined && event.extendedProps.service.options.icon !== undefined && event.extendedProps.service.options.icon.src !== undefined?'<img class="img-bw" width="20" src="' + event.extendedProps.service.options.icon.src + '"/>':''
      },
      getIconClass(event){
        let type = this.isAppointmentCoreSystem(event) ? this.cleanString(event.extendedProps.location):this.getLocationAttribute(event,'type')
        
        switch (type) {
          case 'skype':
          case 3:
            return '<svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="skype" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-skype fa-w-14 fa-lg"><path fill="currentColor" d="M424.7 299.8c2.9-14 4.7-28.9 4.7-43.8 0-113.5-91.9-205.3-205.3-205.3-14.9 0-29.7 1.7-43.8 4.7C161.3 40.7 137.7 32 112 32 50.2 32 0 82.2 0 144c0 25.7 8.7 49.3 23.3 68.2-2.9 14-4.7 28.9-4.7 43.8 0 113.5 91.9 205.3 205.3 205.3 14.9 0 29.7-1.7 43.8-4.7 19 14.6 42.6 23.3 68.2 23.3 61.8 0 112-50.2 112-112 .1-25.6-8.6-49.2-23.2-68.1zm-194.6 91.5c-65.6 0-120.5-29.2-120.5-65 0-16 9-30.6 29.5-30.6 31.2 0 34.1 44.9 88.1 44.9 25.7 0 42.3-11.4 42.3-26.3 0-18.7-16-21.6-42-28-62.5-15.4-117.8-22-117.8-87.2 0-59.2 58.6-81.1 109.1-81.1 55.1 0 110.8 21.9 110.8 55.4 0 16.9-11.4 31.8-30.3 31.8-28.3 0-29.2-33.5-75-33.5-25.7 0-42 7-42 22.5 0 19.8 20.8 21.8 69.1 33 41.4 9.3 90.7 26.8 90.7 77.6 0 59.1-57.1 86.5-112 86.5z" class=""></path></svg>'
          case 'phone':
          case 2:
            return '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="phone" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-phone fa-w-16 fa-lg"><path fill="currentColor" d="M493.4 24.6l-104-24c-11.3-2.6-22.9 3.3-27.5 13.9l-48 112c-4.2 9.8-1.4 21.3 6.9 28l60.6 49.6c-36 76.7-98.9 140.5-177.2 177.2l-49.6-60.6c-6.8-8.3-18.2-11.1-28-6.9l-112 48C3.9 366.5-2 378.1.6 389.4l24 104C27.1 504.2 36.7 512 48 512c256.1 0 464-207.5 464-464 0-11.2-7.7-20.9-18.6-23.4z" class=""></path></svg>'
          case 'physical':
          case 1:
            return '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="map-marked-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-map-marked-alt fa-w-18 fa-lg"><path fill="currentColor" d="M288 0c-69.59 0-126 56.41-126 126 0 56.26 82.35 158.8 113.9 196.02 6.39 7.54 17.82 7.54 24.2 0C331.65 284.8 414 182.26 414 126 414 56.41 357.59 0 288 0zm0 168c-23.2 0-42-18.8-42-42s18.8-42 42-42 42 18.8 42 42-18.8 42-42 42zM20.12 215.95A32.006 32.006 0 0 0 0 245.66v250.32c0 11.32 11.43 19.06 21.94 14.86L160 448V214.92c-8.84-15.98-16.07-31.54-21.25-46.42L20.12 215.95zM288 359.67c-14.07 0-27.38-6.18-36.51-16.96-19.66-23.2-40.57-49.62-59.49-76.72v182l192 64V266c-18.92 27.09-39.82 53.52-59.49 76.72-9.13 10.77-22.44 16.95-36.51 16.95zm266.06-198.51L416 224v288l139.88-55.95A31.996 31.996 0 0 0 576 426.34V176.02c0-11.32-11.43-19.06-21.94-14.86z" class=""></path></svg>'
        }
        let icon = this.getLocationIcon(event)
        return icon !== undefined ? '<img class="img-fluid img-bw" width="20" src="' + icon + '"/>': ''
      },

      getLocationAttribute(event, attrib){
        if(event.extendedProps === undefined || event.extendedProps.location === null || event.extendedProps.location[attrib] === undefined){
          return undefined
        }
        return event.extendedProps.location[attrib]
      },
      getLocationIcon(event){
        let options = this.getLocationAttribute(event, 'options')
        if(options === undefined){
          return undefined
        }
        return options.icon !== undefined && options.icon.src !== undefined ? options.icon.src:undefined
      },


      isAppointmentCoreSystem(event){
        return event.extendedProps !== undefined && typeof event.extendedProps.location == 'string'
      },

    }
}   
</script>

<style >
.client-appointment{
	max-width: 270px;
	background: #f9f9f9;
	padding: .6em;
	border-radius: .3em;
}
.fc-event .img-bw{
  filter: grayscale(1);
  border-radius: 50%;
  width: 15px;
  height: 15px;
}
.fc-event.hover .img-bw{
  filter: none;
}
.rounded-circle.img-fluid.img-max {
    max-width: 40px;
}
</style>