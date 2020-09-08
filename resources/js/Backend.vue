<template>
    <transition name="fade" mode="out-in">
      <div class="wappointment-wrap">
          <div v-if="!db_update">
              <UpdateInformation />
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
import VersionsInfos from './Ne/VersionsInfos'
import PendingDBUpdate from './Ne/PendingDBUpdate'
import UpdateInformation from './Ne/UpdateInformation'
export default {
    components: {VersionsInfos, PendingDBUpdate, UpdateInformation},
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

.sm-text{
    font-size:.9em;
}

.wrounded{
  border-radius: 3rem;
}
.wshadow{
  box-shadow: inset 0px 8px 10px 0 rgba(0,0,0,.08);
}
.wimage{
  max-width: 30px;
}
</style>

