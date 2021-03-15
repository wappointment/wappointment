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
<style >
#buttons-block .btn-secondary.btn-cell.btn-xs{
  padding: .45rem !important;
  font-size: 0.775rem !important;
}
</style>