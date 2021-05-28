<script>

export default {
    data: () => ({
        scriptAsync: null,
        scriptAsyncLoaded: false,
    }),

    mounted(){
        if(!this.isLoadedAlready()){
            this.$WapModal()
            .request(this.preloadScript(),this.successLoadScript)
        }else{
            this.successLoadScript(null)
        }
    },

    methods: {   
         async preloadScript(){
             if( this.scriptAsync === null){
                 throw 'scriptAsync is not defined'
             }
             
            return new Promise((resolve, reject) => {
                let script = document.createElement('script')
                script.onload = (e) => {
                    resolve(true)
                }
                
                script.src = this.scriptAsync
                document.head.appendChild(script)
            })
        },
        successLoadScript(e){
            this.recordToEnv()
            if(this.successAfterLoadScript !== undefined){
                this.successAfterLoadScript(e)
            }
        },
        recordToEnv(){
            if(!this.isLoadedAlready()){
                
                window.loadedScripts.push(this.scriptAsync)
            }
        },
        isLoadedAlready(){
            if(window.loadedScripts === undefined){
                window.loadedScripts = []
            }
            let scriptName = this.scriptAsync
            return window.loadedScripts.find(e => e == scriptName)
        }
        
    },
}
</script>
