<script>
export default {
  methods: {
    strip(html){
      let doc = new DOMParser().parseFromString(html, 'text/html')
      return doc.body.textContent || ""
    },
  },
};
</script>
