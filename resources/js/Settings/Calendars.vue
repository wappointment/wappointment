<template>
  <div v-if="dataLoaded">

    <BreadCrumbs v-if="crumbs.length>0" :crumbs="crumbs" @click="goTo"/>
    <component v-if="currentView !== false" :is="currentView" :key="subCompKey" 
    :subview="subview" v-bind="dynamicProps" @updateCrumb="updateCrumb"></component>
    
    <div class="reduced" v-else>
        ¡Calendar list
    </div>

  </div>
</template>

<script>
import abstractView from '../Views/Abstract'
import Regav from '../RegularAvailability/View'
import Service from '../Views/Subpages/Service'
import hasBreadcrumbs from '../Mixins/hasBreadcrumbs'
import AbstractListing from '../Views/AbstractListing'
import DurationCell from '../BookingForm/DurationCell'

export default {
  extends: abstractView,
  mixins: [ hasBreadcrumbs],
  components:  { 
      Service, Regav ,  AbstractListing, DurationCell
    }, 
  props:['tablabel'],
  created(){
    this.mainCrumbLabel = this.tablabel
  },
  data() {
    return {
      viewName: 'settingscalendars',

    };
  },

};
</script>
<style>

</style>
