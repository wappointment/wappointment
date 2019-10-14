<template>
    <div>
        <div v-if="!changeNow && experience > 0">
            <span :class="'text-'+colorForRating"><fontAwesomeIcon :icon="iconForRating" size="lg"/></span> <button class="btn btn-link btn-sm" @click="changeFeeling">Change how you feel</button>
        </div>
        <div v-else class="d-flex">
            <button class="btn btn-outline-primary mr-2" @click="vote(5)"><fontAwesomeIcon :icon="['far', 'laugh']" size="lg"/> Great</button>
            <button class="btn btn-outline-success mr-2" @click="vote(4)"><fontAwesomeIcon :icon="['far', 'smile']" size="lg"/> Fun</button>
            <button class="btn btn-outline-dark mr-2" @click="vote(3)"><fontAwesomeIcon :icon="['far', 'meh']" size="lg"/> Okay</button>
            <button class="btn btn-outline-warning mr-2" @click="vote(2)"><fontAwesomeIcon :icon="['far', 'frown']" size="lg"/> Bad</button>
            <button class="btn btn-outline-danger mr-2" @click="vote(1)"><fontAwesomeIcon :icon="['far', 'angry']" size="lg"/> Really Bad</button>
        </div>
    </div>
    
</template>
<script>
import { faLaugh, faSmile, faMeh, faFrown, faAngry} from '@fortawesome/free-regular-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faLaugh, faSmile, faMeh, faFrown, faAngry)
export default {
    props:{
        feeling:{
            type:Number,
            default: 0
        }
    },
    components:{ FontAwesomeIcon},
    data: () => ({
        experience:false,
        changeNow: true
    }),
    created(){
        if(this.feeling > 0 ){
            this.experience = this.feeling
            this.changeNow = false
        }
    },
    watch:{
        experience(val){
            this.$emit('changed', val)
        }
    },
    methods: {
        changeFeeling(){
            this.changeNow = true
        },
        vote(val){
            this.experience = val
            this.changeNow = false
        }
    },
    computed: {
        iconForRating(){
            let icon = ''
            if(this.experience == 5) icon = 'laugh'
            if(this.experience == 4) icon = 'smile'
            if(this.experience == 3) icon = 'meh'
            if(this.experience == 2) icon = 'frown'
            if(this.experience == 1) icon = 'angry'
            return ['far', icon]
        },
        colorForRating(){
            if(this.experience == 5) return 'primary'
            if(this.experience == 4) return 'success'
            if(this.experience == 3) return 'black'
            if(this.experience == 2) return 'warning'
            if(this.experience == 1) return 'danger'
        }
    }
}
</script>