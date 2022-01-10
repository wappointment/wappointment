<script>

export default {
    methods: {
        selecti18nSource(source){
            return window.wappointment_i18n[source]
        },
        get_i18n(string, source){
            return this.selecti18nSource(source)[string]
        },

        sprintf_i18n(string, source, values){
            
            let valuesArray = this.i18nPrepareVal(values)
            let stringToProcess = this.selecti18nSource(source)[string]
            for (let i = 0; i < valuesArray.length; i++) {
                const model = valuesArray.length > 1 ?'%'+(i+1)+'$s':'%s'
                stringToProcess = stringToProcess.replace(model, valuesArray[i] )
            }
            return stringToProcess
        },

        i18nPrepareVal(values){
            return Array.isArray(values) ? values:[values]
        }

           
    }
}   
</script>

