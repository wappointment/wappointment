
<template>
    <div class="drag-actions d-flex justify-content-around">
        <DragButton @accepted="goPrev">Previous</DragButton>
        <DragButton @accepted="goNext">Next</DragButton>
    </div>
</template>

<script>
import DragButton from './DragButton'
export default {
    components:{DragButton},
    methods:{
        goPrev(){
            this.$emit('prev');
        },
        goNext(){
            this.$emit('next');
        }
    }
}
</script>
<style>
.drag-actions {
  height: 50px;
  text-align: center;
}
.drag-actions .btn{
    width:100%;
}
</style>