<template >
    <div v-if="getIcon!==undefined" class="wap-img" role="img" :aria-label="desc">
        <FontAwesomeIcon v-if="getIcon.wp_id === undefined" :icon="getIcon" :size="getFaSize"/>
        <div v-else class="wap-icon-image" :class="getClassIcon" :style="getStyle"></div>
    </div>
</template>

<script>

const FontAwesomeIcon = () => import(/* webpackChunkName: "appFawesome" */ '../appFawesome')

export default {
    components: { FontAwesomeIcon },
    props:{
        element:{
            type: Object
        },
        config:{
            type: Object,
            default: ()=>{
                return {mauto:true}
            }
        },
        faIcon:{
            type: [String, Array],
            default: ''
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

            if(this.element !== undefined && [undefined, ''].indexOf(this.element.options.icon) !== -1){
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
            if(this.faIcon != ''){
                return this.faIcon
            }
            return this.element.options.icon
        },
        getFaSize(){
            //'xs', 'sm', '1x', '2x', '3x', '4x', '5x', '6x', '7x', '8x', '9x', '10x'
            if(this.size == 'auto'){
                return '1x'
            }
            if(this.size == 'md'){
                return '2x'
            }
  /*           if(['auto', 'md'].indexOf(this.size) !== -1){
                return ''
            } */
            //console.log('this.size', Date.now(), this.size)
            return this.size
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

.wap-icon-image.xl {
    height: 80px;
    width: 80px;
}

.wap-icon-image.lg {
    height: 50px;
    width: 50px;
}

.wap-icon-image.md, .wap-icon-image.auto {
    height: 25px;
    width: 25px;
}

.wap-icon-image.sm {
    height: 15px;
    width: 15px;
}
.wap-img .fa-lg{
    font-size: 50px;
}
.wap-img .fa-md{
    font-size: 25px;
}

</style>