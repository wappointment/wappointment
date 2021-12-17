<script>
export default {
    data: () => ({
        ctdString: '',
        ctdId: false,
        ctdInitDate: null,
        ctdFormat: '',
    }),
    methods:{
        initCountDown(valueInit, formatString){ // run once the appointment is loaded
            
            this.ctdFormat = formatString
            this.ctdInitDate = new Date()
            this.ctdInitDate.setTime(valueInit*1000)
            // Update the count down every 1 second
            if(this.ctdInitDate.getTime()){
                this.ctdId = setInterval(this.countdownInterval , 1000)
            }
            
        },
        countdownInterval(){
            // mseconds left
            let milisecondsleft = this.ctdInitDate.getTime() - new Date().getTime()

            // return results
            if (milisecondsleft < 0) {
                this.ctdString = ''
                clearInterval(this.ctdId)
            }else{
                this.ctdString = this.ctdFormat
                .replace('[days_left]', Math.floor(milisecondsleft / this.oneDayInMs))
                .replace('[hours_left]', Math.floor((milisecondsleft % this.oneDayInMs) / this.oneHourInMs))
                .replace('[minutes_left]', Math.floor((milisecondsleft % this.oneHourInMs) / this.oneMinInMs))
                .replace('[seconds_left]', Math.floor((milisecondsleft % this.oneMinInMs) / this.oneSecInMs))

            }
        },
        
    },
    computed:{
        oneDayInMs(){
            return this.oneHourInMs * 24
        },
        oneHourInMs(){
            return this.oneMinInMs  * 60
        },
        oneMinInMs(){
            return this.oneSecInMs * 60
        },
        oneSecInMs(){
            return 1000
        },
    }
}
</script>
