<script>

export default {
  computed:{
    getMinText(){
          return this.options.general === undefined || this.options.general.min === undefined ? 'min':this.options.general.min
      },
  },

};
</script>
