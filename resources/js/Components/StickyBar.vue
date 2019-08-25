<template>
    <div id="wappointment_sticky_top">
        <slot></slot>
    </div>
</template>

<script>
export default {

}
</script>
<style>
#wappointment_sticky_top {
  position: sticky;
  width:100%;
  top: 30px;
  padding: 10px;
  z-index: 30;
  left: 160px;
  border-bottom: 1px solid #e2e4e7;
  background-color: #fff;
  box-shadow: 0 .01rem .2rem 0 rgba(0,0,0,.1);
}
#wappointment_sticky_top h1{
    margin-top: 0;
    margin-bottom: 0;
    font-size: 1.6rem;
}
#wappointment_sticky_top h1.h5{
    font-size: 1.3rem;
}

@media (max-width: 961px) { 
    #wappointment_sticky_top {
        left: 36px;
    }
    #wappointment_sticky_top h1{
        font-size: 1.4rem;
    }

}
@media (max-width: 769px) { 
    #wappointment_sticky_top {
        left: 0;
        top: 0;
        padding: .3rem;
    }
    #wpbody select {
        max-height: 20px;
        font-size: 10px;
    }
}

</style>