<script>

export default {

  methods:{
    getImage(method, ext = '.svg'){
        return {
          icon: 'methods/' + method + (method.indexOf('.') === -1 ? ext:''),
          alt: method,
          title: method
        }
      },
  }

};
</script>
