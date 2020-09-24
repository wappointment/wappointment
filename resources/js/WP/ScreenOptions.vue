<template>
    <div>
        <transition name="slide-fade-top">
            <div id="screen-meta" v-if="showSettings" class="metabox-prefs" >
                <div id="screen-options-wrap" >
                    <slot></slot>
                </div>
            </div>
        </transition>
        <div id="screen-meta-links">
            <div id="screen-options-link-wrap" class="hide-if-no-js screen-meta-toggle">
            <button @click="showSettings = !showSettings" type="button" id="show-settings-link" 
            class="button show-settings" aria-controls="screen-options-wrap" aria-expanded="false">Screen Options</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            showSettings: false,
        } 
    },
}
</script>

<style >
.wappointment-wrap #screen-meta{
  display:block;
}
</style>