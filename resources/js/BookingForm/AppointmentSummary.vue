<template>
    <div>
        <div class="wsummary-section wsec-service" v-if="service!== false">
            <div class="wlabel" v-if="hasText(['general','service'])">{{options.general.service}}</div>
            <div class="wselected wmy-4">
                <ElementSelected :service="service" :duration="duration" :cancellable="false"/>
            </div>
        </div>
        <div class="wsummary-section wsec-starts" v-if="startsAt">
            <div class="wlabel">{{options.form.header}}</div>
            <div class="wselected wmy-4">
                {{ startsAt }}
            </div>
        </div>
        <div class="wsummary-section wsec-location" v-if="location">
            <div class="wlabel" v-if="hasText(['general','location'])">{{options.general.location}}</div>
            <div class="wselected wmy-4">
                {{ getLocationLabel }}
            </div>
        </div>
    </div>
</template>

<script>
import ElementSelected from './ElementSelected'
export default {
    props: {
        service: {
            type: [Object, Boolean], 
        },
        duration:{
            type: [Number, Boolean],
        },
        startsAt:{
            type: [String, Boolean],
        },
        location:{
            type: [String],
        },
        options:{
            type: Object
        }
    },
    components: {
        ElementSelected
    },
    computed:{
        getLocationLabel(){
            if(this.location == 'physical') return this.service.address
            if(this.location == 'phone') return this.options.form.byphone
            if(this.location == 'skype') return this.options.form.byskype
        }
    },
    methods:{
        hasText(searchOptions){
            let element = this.options
            for (let i = 0; i < searchOptions.length; i++) {
                if([undefined,''].indexOf(element[searchOptions[i]])){
                    element = element[searchOptions[i]]
                }else{
                    return false
                }
            }
            return true
        }
    }
    
}
</script>
<style>

.wap-front .wsummary-section {
    border-bottom: 1px solid #eee;
    padding: .4em;
    font-size: .8em;
}
.wap-front .wlabel{
    color: #9f9898;
}
.wmy-4{
     margin: .4em 0;
}
.wap-front .wselected{
    border-radius: 1em;
    padding: .2em .7em;
    background-color: #c0acc0;
    color: #fff;
    display: inline-block;
}

</style>

