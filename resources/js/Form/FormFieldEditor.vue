<template>
    <div>
        <label for="exampleFormControlTextarea1">{{ label }}</label>
        <tinymce-editor v-model="updatedValue" :init="config"></tinymce-editor>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
import Editor from '@tinymce/tinymce-vue'

export default {
    mixins: [AbstractField],
    data(){
        return {
            config: {
                menubar: false,
                inline: true,
                toolbar: false,
                plugins: [ 'quickbars lists anchor textcolor link' ],
                quickbars_insert_toolbar: '',
                quickbars_selection_toolbar: 'bold italic link | h2 h3 h4 | bullist numlist | underline forecolor backcolor | removeformat',
                setup: this.setupEditor
            },
            waitForScript: true
        }
    },
    methods:{
        
        setupEditor(editor){
            editor.on('init', this.scriptLoaded);
        },
        scriptLoaded(e){
            this.$emit('loaded')
        }
    },
    components: { 'tinymce-editor': Editor },
}
</script>
<style>
.tox.tox-silver-sink {
    z-index: 9999999;
}
.tox.tox-silver-sink .tox-notifications-container{
    display: none;
}
.tox.tox-silver-sink .tox-pop {
    max-height: 39px !important;
}

.mce-content-body {
    background-color: #f9f9f9;
    padding: .3rem;
    min-height: 80px;
    border: 1px solid #ccc;
    border-radius: .2rem;
    outline: none;
}

.mce-content-body:focus {
    color: #495057;
    background-color: #fff;
    border-color: #c4c3eb;
    outline: 0;
    box-shadow: 0 0 0 .2rem rgba(102,100,203,.25);
}
</style>

