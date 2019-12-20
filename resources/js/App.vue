<template>
    <transition name="fade" mode="out-in">
      <div class="wappointment-wrap">
          <div v-if="!db_update">
              <HelloPage />
              <VersionsInfos />
          </div>
          <div v-else>
              <PendingDBUpdate />
          </div>
          <router-view />
      </div>
    </transition>
</template>

<script>
import VersionsInfos from './Components/VersionsInfos'
import HelloPage from './Components/HelloPage'
import PendingDBUpdate from './Components/PendingDBUpdate'
export default {
    components: {VersionsInfos, HelloPage, PendingDBUpdate},
    data: () => ({
        db_update: false,
    }),
    created(){
        if(window.wappointmentAdmin.hasPendingUpdates!== undefined ){
            this.db_update = true
        }
    },
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

.slide-fade-top-enter-active{
  transition: all .3s ease;
}

.slide-fade-top-leave-active{
  transition: all .1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}

.slide-fade-top-enter, .slide-fade-top-leave-to {
  transform: translateY(-100px);
  opacity: 0;
}

.fade-enter-active{
  transition: all .3s ease;
}

.fade-leave-active{
  transition: all .1s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}

.fade-enter, .fade-leave-to {
  opacity: 0;
  transform:scaleY(0)
}

@media (max-width: 769px) { 
    
    .auto-fold #wpcontent{
        padding-left: 0;
    }
}

.wappointment-wrap p{
    font-size: 1rem;
}
/* hide elements */
#update-nag, .update-nag{
    display: none !important;
}

</style>

