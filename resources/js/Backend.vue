<template>
    <transition name="fade" mode="out-in">
      <div class="wappointment-wrap" >
          <PendingDBUpdate v-if="db_update"/>
          <template v-else>
              <AddonsRequireUpdate v-if="addonsRequiringUpdate.length > 0" :addonsRequiringUpdate="addonsRequiringUpdate" />
              <UpdateInformation v-else />
          </template>
          
          <template v-if="has_messages">
              <transition name="fade" mode="out-in">
                <WPNotice v-if="fully_loaded">
                        <p v-for="messageObj in has_messages">
                            {{ messageObj.message }}
                            <a v-if=" messageObj.link !== undefined" href="javascript:;" @click="goToLink(messageObj.link)">
                                {{ messageObj.link.label }}
                            </a>
                        </p>
                </WPNotice>
              </transition >
          </template>
          <router-view @fullyLoaded="fullyLoaded" />
      </div>
    </transition>
</template>

<script>
import WPNotice from './WP/Notice'
import PendingDBUpdate from './Ne/PendingDBUpdate'
import UpdateInformation from './Ne/UpdateInformation'
import AddonsRequireUpdate from './Ne/AddonsRequireUpdate'
export default {
    components: {PendingDBUpdate, UpdateInformation, AddonsRequireUpdate, WPNotice},
    data: () => ({
        db_update: false,
        has_messages: false,
        fully_loaded: false,
        addonsRequiringUpdate: []
    }),
    created(){
        if(window.wappointmentAdmin.hasPendingUpdates!== undefined ){
            this.db_update = window.wappointmentAdmin.hasPendingUpdates
        }
        if(window.wappointmentAdmin.hasMessages!== undefined ){
            this.has_messages = window.wappointmentAdmin.hasMessages
        }
        this.testAddonsRequireUpdate()
    },
    computed:{
        changing(){ //hack to reset the fullyloaded param every time we change of view
            this.fully_loaded = false
            return this.$route.name
        }
    },
    methods: {
        fullyLoaded(){
            this.fully_loaded = true
        },
        goToLink(linkObj){
            if(linkObj.address.indexOf('[goto_') === 0){
                this.$router.push({ name: linkObj.address.replace('[goto_','').replace(']','')})
            }
            return linkObj.address
        },
        testAddonsRequireUpdate(){
            if(window.wappointmentAdmin.addons !== undefined){
                let addons = window.wappointmentAdmin.addons
                for (const key in addons) {
                    if (addons.hasOwnProperty(key) && addons[key].requires_update !== undefined) {
                        this.addonsRequiringUpdate.push(Object.assign({key:key},addons[key]))
                    }
                }
            }
        }
    }
}
</script>
<style scoped>
@import '../css/bootstrap.css';
</style>
<style>
.btn.btn-secondary.btn-cell{
    position :relative;
    padding:1.4rem;
}
.btn.btn-secondary.btn-cell.selected{
    border: 2px solid var(--primary);
    background: #fff;
}

.btn-secondary.btn-cell .dashicons{
    position :absolute;
    top:5px;
    right:5px;
}

.btn-cell.selected {
    border: 2px solid var(--primary);
}
.btn-link.selected {
   color: #666;
    text-decoration: underline solid var(--primary);
}

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

