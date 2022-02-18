<script>
import browserLang from '../Standalone/browserLang'

export default {
    data: () => ({
        converted:false,
    }),
    methods:{
        
        getFormattedDate(formattedDate, selectedSlot, siteLang, currentTz, fullDateFormat){
            return new Promise((resolve, reject) => {
                if(selectedSlot === false) {
                    resolve(false)
                }else{
                    if(formattedDate === false){
                        if(siteLang!== 'en' && browserLang().substr(0,2)!=='en'){ // if the browser is not english we fetch for a localized date
                            this.convertDateRequest({
                                timezone: this.timeprops.currentTz,
                                timestamp: selectedSlot.start
                            }).then((result) => resolve(result.data.converted)) 
                        }else{
                            resolve(this.getMoment(selectedSlot.start, currentTz).format(fullDateFormat))
                        }
                    }
                }
                
            })
        },

        async convertDateRequest(data) {
            return await this.serviceBooking.call('convertDate', data)
        }, 

    }
}
</script>
