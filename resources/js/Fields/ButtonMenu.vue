
<template>
    <div>
        <div class="dropdown d-flex align-self-center" :class="{'show': toggle}" v-if="elements.length > 1">
            <button class="btn btn-link dropdown-toggle btn-xs" type="button" @click="toggle=!toggle">
                {{ funcDisplay(current) }} 
            </button>
            <div class="dropdown-menu" :class="{'show': toggle}">
                <a class="dropdown-item" href="javascript:;" v-for="element in elements" @click="selectElement(element)"> {{ funcDisplay(element) }} </a>
            </div>
        </div>
        <div v-else class="align-self-center text-muted">
            {{ funcDisplay(current) }} 
        </div>
    </div>
</template>

<script>
export default {
    props: [ 'elements', 'current', 'funcDisplay'],
    data: () => ({
        toggle: false
    }),
    methods:{
        selectElement(element){
            this.$emit('selected', element)
            this.toggle = false
        }
    }
}
</script>