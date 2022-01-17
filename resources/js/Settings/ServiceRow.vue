<template>
    <tr class="row-click">
        <td>
            <div>{{ idx + 1 }}</div> 
        </td>
        <td>
            <div>
                <div class="d-flex align-items-center">
                    <WapImage v-if="serviceHasIcon(service)" :element="service" :config="{mauto:false}" :desc="service.name" size="lg" /> 
                    <div class="ml-2">
                        <div>{{ service.name }}</div>
                        <div v-if="service.labels !== undefined" class="d-flex align-items-center">
                            <div v-for="label in service.labels" class="mr-2" :class="label.class">{{ label.text }}</div>
                        </div>
                        <div v-else class="small">
                            <div v-if="isSellable(service)" class="text-success">{{ get_i18n( 'selling', 'orders') }}</div>
                            <div v-else class="text-info">{{ get_i18n( 'free', 'orders') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wlist-actions text-muted">
                <span :data-tt="get_i18n( 'sort', 'common')" v-if="can_move" ><span class="dashicons dashicons-move"></span></span>
                <span :data-tt="get_i18n( 'getshort', 'common')"><span class="dashicons dashicons-shortcode" @click.prevent.stop="$emit('shortcode', service.id)"></span></span>
                <span :data-tt="get_i18n( 'edit', 'common')"><span class="dashicons dashicons-edit" @click.prevent.stop="$emit('edit', service)"></span></span>
                <span :data-tt="get_i18n( 'delete', 'common')"  ><span class="dashicons dashicons-trash" @click.prevent.stop="$emit('delete', service.id)"></span></span>
                <span>(id: {{ service.id }})</span>
            </div>
        </td>
        <td>
            <div class="d-flex">
                <WCell class="duration-cell" v-for="(durationObj,dkey) in getDurations(service)" :key="dkey">
                    <span>{{durationObj.duration}}min</span><span v-if="isSellable(service) && soldDuration(durationObj)">{{ getDurationPrice(durationObj) }}</span>
                </WCell>
            </div>
        </td>
        <td>
            <div class="d-flex">
                <div v-for="locationObj in getLocations(service)" class="location d-flex align-items-center" :data-tt="locationObj.name">
                    <WapImage :element="locationObj" :desc="locationObj.name" size="md" />
                </div>
            </div>
        </td>
    </tr>
</template>

<script>

import WCell from '../WComp/WCell'
import CanFormatPrice from '../Mixins/CanFormatPrice'
export default {
    props: ['service', 'idx', 'can_move'],
    mixins: [CanFormatPrice],
    components: {WCell},
    methods: {
        soldDuration(durationObj){
            return ['',undefined,false].indexOf(durationObj.woo_price) === -1
        },
        getDurationPrice(durationObj){
            return this.formatPrice(durationObj.woo_price)
        },
        isSellable(service){
            return service.options.woo_sellable
        },
        serviceHasIcon(service){
            return service.options.icon != ''
        },
        getDurations(service){
            return service.options.durations
        },
        getLocations(service){
            return service.locations
        },
    }
}   
</script>
<style>
.location {
    margin: .2rem;
    color: #717171;
}
.wcell.duration-cell{
    padding: 0;
}
.wcell.duration-cell span{
    display: inline-block;
    padding: .3em;
}

.wcell.duration-cell span:nth-child(2) {
    background: #fbfbfb;
    border-left:1px solid #ccc;
}
</style>