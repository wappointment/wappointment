import VueTelInput from './vue-tel-input.vue';

export default {
  ...VueTelInput,
  install: (Vue) => {
    Vue.component('VueTelInput', VueTelInput);
    return Vue;
  },
};
