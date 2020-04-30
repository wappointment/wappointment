<script>

export default {

    methods: {

        typeLabel(type, typesLabels){
          for (let i = 0; i < typesLabels.length; i++) {
            const element = typesLabels[i]
            if(element.code == type){
              return element.name
            }
          }
      },

    }
};
</script>
