<template>
    <span class="value-card d-flex align-items-center justify-content-between" >
        <span class="label"><slot></slot></span>
        <span v-if="canDiscard" class="close" @click.prevent.stop="discard"></span>
    </span>
</template>
<script>
export default {
    props:{
        value:{
        },
        canDiscard:{
            type: Boolean,
            default:true
        }
    },
    methods: {
      discard(){
          this.$emit('discard', this.value)
      }
    }
}
</script>

<style>
.value-card {
    background-color: #ababab;
    padding: .1rem .2rem .1rem .2rem;
    font-size: .8rem;
    border-radius: .2rem;
    position: relative;
    margin: 0 .6rem .3rem 0;
    color:#fff;
    border: 1px solid #9d9d9d;
    font-weight: bold;
}
.value-card:hover {
    background-color: #7d7d7d;
}
.value-card .close{
    background: #fff;
    width: 15px;
    display: inline-block;
    height: 15px;
    border-radius: 2rem;
    position: relative;
    margin-left: .4rem;
}
.value-card .close::after {
    transform: translateX(15px) rotate(-45deg);
}
.value-card .close::before, .value-card .close::after {
    content: ' ';
    position: absolute;
    background-color: #b5b1b1;
}
.value-card .close::before {
    transform: translateX(15px) rotate(45deg);
}
.value-card .close:hover::before, .value-card .close:hover::after {
    background-color: #575656;
    width: 2px;
}


.value-card .close::before, .value-card .close::after  {
    height: 10px;
    width: 1px;
    top: 3px;
    right: 22px;
}
</style>


