<script>
export default {
  methods: {
    dotKeyCheck(prop){
        return typeof prop === 'array' ? prop:this.dotStringPropertyToArray(prop)
    },
    dotStringPropertyToArray(prop){
        return prop.indexOf('.') === -1 ? [prop] : prop.split('.')
    },
    dotGetKeyOrSubKeyElement(element, keyProp){
        if(element === undefined) return false
        let newElement = element
        for (let i = 0; i < keyProp.length; i++) {
            if(newElement[keyProp[i]] === undefined) return false
            newElement = newElement[keyProp[i]]
        }
        return newElement
    },
  },
};
</script>
