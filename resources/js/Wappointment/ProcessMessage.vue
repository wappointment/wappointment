<template>
    <span>
      {{ messageParsed }} <a v-if="linkFound" :href="linkFound" target="_blank">{{ linkFound }}</a>
    </span>
</template>

<script>

export default {
    props: ['message'],
    data: () => ({
        linkFound: false,
    }),
    computed: {
      messageParsed(){
        let lookForLink = 'https://'
        if(typeof this.message == 'object'){
          return this.message.message
        }
        if(typeof this.message == 'string'){
          if(this.message.indexOf(lookForLink) === -1 ){
            return this.message
          }else{
            let split = this.message.split(lookForLink)
            this.linkFound = lookForLink + split[1]
            return split[0]
          }
        }else{
          return this.message
        }
        
      }
    },
    
}
</script>