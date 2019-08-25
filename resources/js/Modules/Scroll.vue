<script>
export default {

  methods: {
      scrollToElement( className ) {
        document.querySelector(className).scrollIntoView({ 
          behavior: 'smooth' 
        });
      },
      scrollToTop() {
          window.scroll({
            top: 0, 
            left: 0, 
            behavior: 'smooth' 
          });
      },   
    }
}
</script>