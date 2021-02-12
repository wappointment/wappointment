
<script>
export default {

    methods:{
      hoverIndicatorRegister(){
        let selectedTz = this.selectedTimezone 
        window.jQuery('.fc-content-today').on('mouseover','.fc-now-indicator-line', function(now, e) {
            window.jQuery('.fc-now-indicator-line .nowtime').html(now.tz(selectedTz).format('dddd HH:mm'))
            window.jQuery('.fc-now-indicator-line .nowtime').addClass('show')
        }.bind(null,  this.momenttz.tz(this.viewData.now,this.viewData.timezone)))

        window.jQuery('.fc-content-today').on('mouseout','.fc-now-indicator-line',() => window.jQuery('.nowtime').removeClass('show'))
      }, 
      hoverIndicatorUnregister(){

        window.jQuery('.fc-content-today').unbind('mouseover')
        window.jQuery('.fc-content-today').unbind('mouseout')
      }, 
      beforeDestroy(){
          this.hoverIndicatorUnregister()
          this.observer.disconnect()
      },
      setTodayAsPast(){
        if(this.momenttz.tz(this.viewData.now,this.viewData.timezone).hour() >= this.maxHour){

              let all = document.querySelectorAll('.fc-today')
              for (let i = 0; i < all.length; i++) {
                all[i].classList.add('fc-past')
              }

          }
      },
        observeNowIndicator(){
          if(this.observer !== undefined){
              this.hoverIndicatorUnregister()
              this.observer.disconnect()
          }
          this.observer = undefined
          const elements = document.querySelectorAll('.fc-content-skeleton td .fc-content-col')
          let today = false
          for (let i = 0; i < elements.length; i++) {
                if(elements[i].childNodes.length > 0){
                    for (let j = 0; j < elements[i].childNodes.length; j++) {
                        if(Array.from(elements[i].childNodes[j].classList).indexOf('fc-now-indicator-line') !== -1) {
                            today = elements[i]
                            today.classList.toggle('fc-content-today')
                        }
                        if(today !== false) break
                    }
                    
                }
              if(today !== false) break
          }
          if(today !== false){
              // config object
              const config = {
                  attributes: false,
                  attributeOldValue: false,
                  characterData: false,
                  characterDataOldValue: false,
                  childList: true,
                  subtree: false
              };
              // instantiating observer
              this.observer = new MutationObserver(this.observerSubscriber)

              // observing target
              this.observer.observe(today, config)
              this.hoverIndicatorRegister()
          }
      },
      observerSubscriber(mutations) {
          for (let i = 0; i < mutations.length; i++) {
            const mutation = mutations[i]
            if (mutation.addedNodes.length > 0) {
                  for (let k = 0; k < mutation.addedNodes.length; k++) {
                    if( Array.from(mutation.addedNodes[k].classList).indexOf('fc-now-indicator-line')!==-1){
                      this.setTodayPastSection()
                    }
                  }
              }
          }
      },
      setTodayPastSection(){
        if([0, '0', '0px'].indexOf(window.jQuery('.fc-now-indicator-line').css('top')) === -1){
          window.jQuery('.fc-now-indicator-line').css('height',window.jQuery('.fc-now-indicator-line').css('top'))
          window.jQuery('.fc-now-indicator-line').css('top', 0)
        }

        window.jQuery('<div>', {class: 'nowtime', html:''}).appendTo( window.jQuery( '.fc-now-indicator-line' ) )
          
      },

      setDaysProperties(){

        if(this.daysProperties !== false) return
        let daysProperties = []
        window.jQuery('.fc-day.fc-widget-content').each(function( index ) {
          if(window.jQuery(this).hasClass('fc-past') ) daysProperties.push('fc-past')
          else{
            daysProperties.push('')
          } 
        })
        this.daysProperties = daysProperties
      },
      setSkeletonProperties(){

        let daysProperties = this.daysProperties
        window.jQuery('.fc-content-skeleton tr td').each(function( index ) {
          if(window.jQuery(this).hasClass('fc-axis')){

          }else{
            if(daysProperties[index-1]=='fc-past') {
              window.jQuery(this).addClass('skel-past')
            }
          }
          
        })
      },
      isParentInThePast(element){
        if(this.hasBeenSetCalProps){
          return
        }
        this.setDaysProperties()
        this.setSkeletonProperties()
        this.setTodayAsPast()
        this.hasBeenSetCalProps = true
      },
    }
}
</script>