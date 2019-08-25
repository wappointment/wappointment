<template>
    <div @click="changed" class="icon hovercursor" :class="getPassedClass" >
        <span title="email" class="dashicons" 
        :class="[isPublished ? 'dashicons-yes' : 'dashicons-no' ]" ></span>
        <span class="dashicons dashicons-email-alt"></span>
    </div>
</template>

<script>
export default {
    props: ['element', 'value', 'passedClass'],
    computed: {
        isPublished(){
            if(this.value !== undefined){
                 return this.value
            }
            return this.element.published === true
        },
        getPassedClass(){
          let classesVar = {'published' : this.isPublished}
          if(this.passedClass === undefined) classesVar['mr-2'] = true
          else classesVar[this.passedClass] = true

          return classesVar
        }
    },
    methods: {
        changed(){
            this.$emit('changed', this.value !== undefined ? !this.isPublished : Object.assign(this.element,{ published: !this.isPublished }))
        },
    }
}   
</script>
<style>
.icon .dashicons{
  font-size: 2rem;
  color: #d8d7d7;
  height: 2rem;
  width: 2rem;
}
.hovercursor{
  cursor: pointer;
}
.icon{
  position: relative;
}

.icon.published .dashicons-yes, 
.icon .dashicons-no{
  position: absolute;
  top: 12px;
  left: 12px;
  font-size: 20px;
  text-shadow: -3px 0 #fff, 0 3px #fff, 3px 0 #fff, 0 -3px #fff;
}
.icon.published .dashicons-yes{
  color: #66b36d;
}
.icon .dashicons-no{
  color: #b37866;
}
.icon.published .dashicons-email-alt{
  color:#b1b1b1;
}
.icon:hover .dashicons-email-alt{
  cursor:pointer;
  color:#969696;
}
</style>
