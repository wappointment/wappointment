
<template>
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <div data-tt="Booking button's title"><label><InputPh v-model="titleGiven" ph="Button title"/></label></div>
            <div data-tt="Center the widget within the container"><label><input type="checkbox" v-model="center"> Center</label></div>
            <div data-tt="Opens the calendar's step automatically"><label><input type="checkbox" v-model="open"> Auto-open Calendar</label></div>
            <div data-tt="Calendar will expand to the container's width"><label><input type="checkbox" v-model="large"> Full width Calendar</label></div>
            <div v-if="!preview" data-tt="Show a week view instead of the full month"><label><input type="checkbox" v-model="week"> Week view</label></div>
        </div>
        <div v-if="preview">
            <img :src="previewSCimg" class="img-fluid" alt="preview booking button shape" width="100" />
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
        center: false,
        week:false,
        titleGiven: ''
    }),
    created(){
        this.titleGiven = this.title
    },
    computed:{
        getShortCode(){
            let shortcode = 'wap_widget title="'+this.titleGiven+'"'
            shortcode += this.large? ' large ':'' 
            shortcode += this.open? ' open ':''
            shortcode += this.center? ' center ':''
            shortcode += this.week? ' week ':''
            shortcode = '['+shortcode+']'
            
            this.$emit('change', shortcode, {
                largeVersion: this.large,
                autoOpen: this.open,
                week: this.week,
                buttonTitle: this.titleGiven
            })
            return shortcode
        },
        previewSCimg(){
            let image = 'widget_' + (this.open ? 'cal_':'') + (this.large ? 'lg':(this.center ? 'sm-c':'sm')) + '.svg'
            return window.apiWappointment.resourcesUrl +'images/' + image
        },
    }
}   
</script>