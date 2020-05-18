<template>
    <span class="customfield" @click="selectCustomField">{{ fieldname }}</span>
</template>
<script>
export default {

      // there are some props available
      // `node` is a Prosemirror Node Object
      // `updateAttrs` is a function to update attributes defined in `schema`
      // `editable` is the global editor prop whether the content can be edited
      props: ['node', 'updateAttrs', 'editable'],
      data() {
        return {
          // save the iframe src in a new variable because `this.node.attrs` is immutable
          fieldname: this.node.attrs.src + ':' + this.node.attrs.alt,
        }
      },
      methods:{
        selectCustomField(a,b,c){
          this.selectElementText(a.target)
        },
        selectElementText(el) {
            this.removeTextSelections();
            if (document.selection) {
                var range = document.body.createTextRange();
                range.moveToElementText(el);
                range.select();
            }
            else if (window.getSelection) {
                var range = document.createRange();
                range.selectNode(el);
                window.getSelection().addRange(range);
            }
        },
        removeTextSelections() {
          if (document.selection) document.selection.empty(); 
          else if (window.getSelection) window.getSelection().removeAllRanges();
        }
      }
}
</script>
<style>
.customfield {
  border-radius: .4rem;
  padding: .2rem .2rem;
  background-color: #fff;
  color: var(--primary);
  cursor: grab;
  border: 1px dashed var(--primary);
}
</style>

