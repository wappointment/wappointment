<template>
  <ClientsPage />
</template>

<script>

import ClientsPageBase from './Clients'
let ClientsPage  = window.wappointmentExtends.filter('ClientsPage', ClientsPageBase)
export default {
    components: {ClientsPage},
}   

</script>
