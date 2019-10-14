<template>
    <transition name="fade" mode="out-in">
      <div class="wappointment-wrap">
          <HelloPage></HelloPage>
          <VersionsInfos></VersionsInfos>
          <router-view></router-view>
      </div>
    </transition>
</template>

<script>
import VersionsInfos from './Components/VersionsInfos'
import HelloPage from './Components/HelloPage'
export default {
    components: {VersionsInfos, HelloPage},
}
</script>
<style scoped>
@import '../css/bootstrap.css';
</style>
<style>
#wpcontent{
  padding-left: 0;
}


.modal {
    z-index: 50 !important;
}
.modal-backdrop {
    z-index: 40 !important;
}
.wp-core-ui .button.button-block {
    display: block;
    width: 100%;
}

.wp-core-ui .button.button-xl {
    height: 50px;
    font-size: 1.4rem;
    line-height: 48px;
    padding: 0 12px 2px;
}

.btn.active{
    content: "\f147";
}

.vue-form-generator .field-radios .radio-list label {
    display: inline-block;
    margin-right: 1rem;
}

.vue-form-generator fieldset {
    margin: 1rem 0;
}

.vue-form-generator .field-wrap {
    display: block !important;
}

 h1{
    margin-top: 1rem;
}

.btn-xs {
    padding: .25rem !important;
    font-size: 0.675rem !important;
    line-height: 1.5;
    border-radius: 0.2rem;
}

.reduced{
    max-width: 670px;
}
.modal-dialog{
    max-width:590px;
}
.vue-form-generator fieldset {
    margin: 0 !important;
}
.vue-form-generator .radio-list {
    display: -ms-flexbox !important;
    display: flex !important;
}
.vue-form-generator .radio-list label {
    margin-right: 4px;
}
.text-success{
    color: #66b36d;
}

@media (max-width: 769px) { 
    
    .auto-fold #wpcontent{
        padding-left: 0;
    }
}

/* hide elements */
#update-nag, .update-nag{
    display: none !important;
}

</style>

