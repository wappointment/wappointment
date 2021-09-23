
<template>
    <WCell v-if="duration" @clicked="$emit('clicked',duration)" :class="{'wbtn wbtn-cell wbtn-secondary':!show}">
        <div class="duration-value">{{ duration }}min</div>
        <slot />
    </WCell>
</template>

<script>
import WCell from '../WComp/WCell'
export default {
    props: {
        duration:{type:[Boolean,Number]},
        show:{type: Boolean, default:false},
    },
    components:{WCell}
}
</script>

