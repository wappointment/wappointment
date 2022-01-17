
<template>
    <div class="d-flex ddays" >
        <div v-for="(dayH, idy) in weekHeader">
            {{ initial(dayH) }}
        </div>
    </div>
</template>

<script>

import browserLang from '../Standalone/browserLang'
export default {
    props: ['weekHeader',],
    methods: {
        initial(string){
            if(browserLang().indexOf('zh') === 0){ //in chinese the first character makes no sense we keep only the 3rd
                return string.toUpperCase().substring(2, 3)
            }
            return string.toUpperCase().substring(0, 1)
        },
    },
}
</script>