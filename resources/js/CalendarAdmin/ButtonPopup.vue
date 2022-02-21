<template>
    <div class="btn btn-secondary mr-md-2 align-items-center" @click="$emit('click')" :class="{'fdisabled' :disabled}">
        <div class="dashicons" :class="classIcon"></div>
        <div class="text-center">
            <p class="h6 m-0">{{ title }}</p>
            <p class="small m-0">{{ subtitle }}</p>
        </div>
    </div>
</template>

<script>
export default {
    props: ['disabled', 'classIcon', 'title', 'subtitle'],
}
</script>
<style>
.fdisabled{
  cursor: not-allowed !important;
  color: #c8c8c8 !important;  
}

.fdisabled .dashicons.txt{
  color:#c8c8c8 !important;
}

</style>