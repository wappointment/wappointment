<template>
    <div>
        <button v-if="!fileDetected" class="btn btn-secondary" @click.prevent.stop="openFileInput">{{ label }}</button>
        <div v-else class="btn" @click.prevent.stop="openFileInput"> 
            <img :src="fileDetected" alt="" class="img-fluid img-round" width="100"></div>
        <input ref="fileinput" id="file" @change="onFileChange" type="file" class="form-control-file d-none"/>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
export default {
    name: 'core-upload',
    mixins: [AbstractField],
    computed:{
        fileDetected(){
            if(this.parentModel !== undefined && this.parentModel.media !== undefined){
                for (let i = 0; i < this.parentModel.media.length; i++) {
                    const element = this.parentModel.media[i]
                    if(element.pivot !== undefined && element.pivot.tag == this.model){
                        return element.url + '?time='+Date.now()
                    }
                    
                }
            }
            return false
        }
    },
    methods: {
        openFileInput(){
            this.$refs.fileinput.click()
        },
        onFileChange(e) {
            var files = e.target.files || e.dataTransfer.files;
            if (files.length == 0) return
            
            this.updatedValue = this.convertFileArray(files)
        },
        convertFileArray(files){
            let filesArray = []
            for (let i = 0; i < files.length; i++) {
                filesArray.push(files[i])
            }
            return filesArray
        }
    }
}
</script>

