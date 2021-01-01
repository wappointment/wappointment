<template>
  <div >
      <Widget :stepsAllowed="['Customize', 'Insert']"/>
  </div>
</template>

<script>
import abstractView from '../Views/Abstract'
import Widget from '../Settings/WidgetShow'

export default {
  extends: abstractView,
  components: {Widget},

};
</script>