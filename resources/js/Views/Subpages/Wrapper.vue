<template>
      <router-view @fullyLoaded="$emit('fullyLoaded')"></router-view>
</template>

<style>
.full-width.about-wrap .notice, .full-width.about-wrap div.error, .full-width.about-wrap div.updated {
    display: block !important;
}
</style>