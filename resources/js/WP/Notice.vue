<template>
    <transition name="fade-in">
        <div class="d-flex align-items-center notice" :class="'notice-' + type">
              <div class="notice-wappointment"><span class="dashicons-before dashicons-wappointment text-primary"></span></div>
              <div class="ml-2 notice-text"><slot></slot></div>
              <div class="clear"></div>
        </div>
    </transition>
</template>

<script>
import ContactButton from '../Wappointment/ContactButton'
export default {
  components: {ContactButton},
    props: {
        type: {
            type: String,
            default: 'error'
        },
    },
}
</script>