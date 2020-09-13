<template >
    <div v-if="getIcon!==undefined" role="img" :aria-label="desc">
        <FontAwesomeIcon v-if="getIcon.wp_id === undefined" :icon="getIcon" :size="size"/>
        <div v-else class="wap-icon-image" :class="getClassIcon" :style="getStyle"></div>
    </div>
</template>

<script>

const FontAwesomeIcon = () => import(/* webpackChunkName: "appFawesome" */ '../appFawesome')

export default {
    components: { FontAwesomeIcon },
    props:{
        element:{
            type:Object
        },
        config:{
            type:Object,
            default: ()=>{
                return {mauto:true}
            }
        },
        size:{
            type: String,
            default: 'lg'
        },
        desc:{
            type: String,
            default: ''
        }
    },
    computed:{
        getIcon(){

            if([undefined, ''].indexOf(this.element.options.icon) !== -1){
                switch(this.element.type) {
                    case 3:
                    case '3':
                    case 'skype':
                        return ['fab', 'skype']
                    case 2:
                    case '2':
                    case 'phone':
                        return 'phone'
                    case 1:
                    case '1':
                    case 'physical':
                        return 'map-marked-alt'
                    default:
                        return undefined
                }
            }

            return this.element.options.icon
        },
        getClassIcon(){
            let classes = {}

            classes.mauto = this.config.mauto
            classes.wshadow = true
            classes.wrounded = true
            classes[this.size] = true     
        
            return classes
        },
        getStyle(){
            return {
                backgroundImage: 'url('+ this.getIcon.src +')'
            }
        }
    },

}   
</script>
<style>
.wap-icon-image{
    width: 25px;
    text-align: center;
}
.wap-icon-image.mauto{
    margin: 0 auto;
}
.wap-icon-image img{
    max-width: 100%;
    height: auto;
    border-radius: .25rem
}
.wap-icon-image {
    background-position: center center;
    background-size: cover;
}

.wap-icon-image.lg {
    height: 80px;
    width: 80px;
}

</style>