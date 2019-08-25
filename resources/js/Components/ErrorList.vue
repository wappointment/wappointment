<template>
    <div class="updated error" :class="className" v-if="Object.keys(errors).length > 0">
        <p class="h5">{{ mainError }}</p>
        <ol class="small">
            <li v-for="error in flattenErrorMessages">
                {{ error }}
            </li>
        </ol>
        <div v-if="hasDebug">
          <a href="javascript:;" @click="showDebug = !showDebug">Show full error</a>
          <ol v-if="showDebug">
            <li v-for="error in flattenDebugMessages">
                {{ error }}
            </li>
          </ol>
        </div>
        
    </div>
</template>

<script>
export default {
  props: ['errors', 'className'],
  data() {
    return {
        showDebug: false,
    }
  },


  computed: {
    mainError(){
      return (this.errors.default[0]!== undefined) ? this.errors.default[0]:'Unknown error occured'
    },
    flattenErrorMessages() {
      let messages = [];

      for (const key in this.errors.validations) {
        if (key!= 'debug' && this.errors.validations.hasOwnProperty(key)) {
          const element = this.errors.validations[key];
          for (const subkey in element) {
            if (element.hasOwnProperty(subkey)) {
              const subelement = element[subkey];
              messages = _.concat(messages, subelement);
            }
          }

        }
      }

      return messages;
    },
    hasDebug(){
      return this.errors.hasOwnProperty('debug') && this.errors.debug.length > 0
    },
    flattenDebugMessages() {
      let messages = [];

      for (const key in this.errors) {
        if (key== 'debug' && this.errors.hasOwnProperty(key)) {
          const element = this.errors[key];
          messages = _.concat(messages, element);
        }
      }

      return messages;
    },
  },

}
</script>

