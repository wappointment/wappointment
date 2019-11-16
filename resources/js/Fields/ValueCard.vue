<template>
    <span class="value-card" >
        <span class="label"><slot></slot></span>
        <span class="close" @click.prevent.stop="discard"></span>
    </span>
</template>
<script>
import copy from 'copy-to-clipboard'
export default {
    props:['value'],
    methods: {
      discard(){
          this.$emit('discard', this.value)
      }
    }
}
</script>

<style>
.value-card {
    background-color: #ececec;
    padding: .1rem 1rem .1rem .2rem;
    font-size: .8rem;
    border-radius: .2rem;
    position: relative;
    margin: 0 .6rem .3rem 0;
    border: 1px solid #dfdfdf;
}
.value-card:hover {
    background-color: #f4f4f4;
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
    right: 24px;
}
</style>


