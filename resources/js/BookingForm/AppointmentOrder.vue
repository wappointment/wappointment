<template>
    <div>
        <div class="wcharge wsummary-section" v-for="charge in order.prices">
            <div class="wselected wclosable wmy-4 d-flex align-items-center d-flex-inline" >
                <span class="welementname">{{ charge.price.name }} - {{ formatCentsPrice(charge.price.price) }}</span>
            </div>
        </div>
        <div class="wtotal wsummary-section">
            Total: <strong>{{ formatCentsPrice(order.total) }}</strong>
        </div>
    </div>
</template>

<script>
import CanFormatPrice from '../Mixins/CanFormatPrice'
export default {
    mixins:[CanFormatPrice],
    props: {
        order: {
            type: Object, 
        },
    },
}
</script>
<style>
.wcharge {
    font-size: .8em;
}
.wtotal.wsummary-section{
    padding-left:1.1em;
}

</style>
