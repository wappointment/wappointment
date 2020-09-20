<template>
    <span class="value-card wclosable d-flex align-items-center justify-content-between" >
        <span class="label"><slot></slot></span>
        <span v-if="canDiscard" class="wclose" @click.prevent.stop="discard"></span>
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
@import '../../css/closable.css';
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
.value-card.wclosable .close{
    background: #fff;
    border-radius: 2em;
}
.value-card.wclosable .wclose::before, .value-card.wclosable .wclose::after {
    background-color: #b5b1b1;
}

</style>


