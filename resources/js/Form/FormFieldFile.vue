<template>
    <div class="form-group">
        <label for="file">{{ label }}</label>
        <input id="file" @change="onFileChange" type="file" class="form-control-file"/>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
export default {
    name:'core-file',
    mixins: [AbstractField],
    methods: {
        onFileChange(e) {
            var files = e.target.files || e.dataTransfer.files
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
