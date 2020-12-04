<template>
  <div class="container m-4">
    <h1>Wappointment can't work properly</h1>
    <p>An issue occured while loading the page.</p>

    <Notifications :messages="errorDetails" title="Error loading that screen"></Notifications>
  </div>
</template>

<script>

const Notifications = () => import(/* webpackChunkName: "wappo-notif" */ '../WP/Notifications')
export default {
  components: { Notifications },
  computed:{
    errorDetails(){
      return [window.backWappoError]
    }
  }
}
</script>
