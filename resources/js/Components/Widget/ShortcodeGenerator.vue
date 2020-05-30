
<template>
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <div data-tt="Opens the calendar's step automatically"><label><input type="checkbox" v-model="open"> Auto-open Calendar</label></div>
            <div data-tt="Calendar will expand to the container's width"><label><input type="checkbox" v-model="large"> Full width Calendar</label></div>
            <div v-if="!preview" data-tt="Show a week view instead of the full month"><label><input type="checkbox" v-model="week"> Week view</label></div>
        </div>
        <div v-if="preview">
            <img :src="previewSCimg" class="img-fluid" width="100">
        </div>
        <span style="display:none;">{{ getShortCode}}</span>
    </div>
</template>

<script>
export default {
    props: {
        preview:{
            type:Boolean,
            default:true
        },
        title:{
            type:String,
            default:''
        },
    },
    data: () => ({
        large:false,
        open:false,
        week:false,
    }),
    computed:{
        getShortCode(){
            let shortcode = 'wap_widget title="'+this.title+'"'
            shortcode += this.large? ' large ':'' 
            shortcode += this.open? ' open ':''
            shortcode += this.week? ' week ':''
            shortcode = '['+shortcode+']'
            
            this.$emit('change', shortcode, {
                largeVersion: this.large,
                autoOpen: this.open,
                week: this.week
            })
            return shortcode
        },
        previewSCimg(){
            let image = 'widget_' + (this.open ? 'cal_':'') + (this.large ? 'lg':'sm') + '.svg'
            return window.apiWappointment.resourcesUrl +'images/' + image
        },
    }
}   
</script>