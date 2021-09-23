
<template>
    <div class="bg-danger rounded p-2">
        <div class="text-white" >
            {{currencyText}} is not supported 
        </div>
        <div class="text-muted small bg-secondary p-2 rounded" >
            Accepted currencies : {{ currencies }}
        </div>
    </div>
</template>

<script>
import CanFormatPrice from '../Mixins/CanFormatPrice'
export default {
    mixins: [CanFormatPrice],
    props:['currencies'],
}
</script>


