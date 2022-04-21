<template>
    <div>
        <div class="table-hover">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ get_i18n('client', 'clients') }}</th>
                        <th scope="col">{{ get_i18n('phone', 'clients') }}</th>
                        <th scope="col">{{ get_i18n('skype', 'clients') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="row-click" v-if="elements.length > 0" v-for="(client, idx) in elements">
                        <td>
                            <div>{{ idx + 1 }} </div> 
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div v-if="client.avatar !== ''">
                                    <img :src="client.avatar" :alt="client.name" class="border border-secondary wrounded mr-2">
                                </div>
                                <div>
                                    <div>{{ client.name }} <span v-if="owes(client)" class="owes bg-warning p-1 rounded text-white">{{ get_i18n('owes', 'clients') }} {{owes(client)}}</span></div>
                                    <div>{{ client.email }} </div>
                                    <div><span class="dashicons dashicons-admin-site-alt3"></span> {{ getTimezone(client) }} </div>
                                </div>
                            </div>
                            <div class="wlist-actions text-muted">
                                <span :data-tt="get_i18n('edit', 'common')" v-if="canEditClient">
                                    <span class="dashicons dashicons-edit" @click.prevent.stop="$emit('editClient', client)"></span>
                                </span>
                                <span :data-tt="get_i18n('delete', 'common')" v-if="canDeleteClient">
                                    <span class="dashicons dashicons-trash" @click.prevent.stop="$emit('deleteClient', client.id)"></span>
                                </span>
                            </div>
                            
                        </td>
                        <td>
                            {{ getPhone(client) }}
                        </td>
                        <td>
                            {{ getSkype(client) }}
                        </td>
                    </tr>
                    <tr v-else>
                        {{get_i18n('empty_listing', 'common')}}
                    </tr>
                </tbody>
            </table>
        </div>
        <Pagination v-if="isPaginated"  :pagination="pagination" @changePage="changePage"/>
    </div>
</template>

<script>

import ClientsService from '../Services/V1/Client'
import AbstractListing from './AbstractListing'
import hasPermissions from '../Mixins/hasPermissions'
import CanFormatPrice from '../Mixins/CanFormatPrice'
export default {
    label: 'All',
    extends: AbstractListing,
    mixins:[hasPermissions, CanFormatPrice],
    created(){
        this.mainService = this.$vueService(new ClientsService)
    },
    data: () => ({
        keyDataSource:'clients'
    }),
    methods: {
        hasOption(client){
          return [undefined,null,''].indexOf(client.options) === -1
        },
        owes(client){
            return this.hasOption(client) && client.options.owes !== undefined && client.options.owes > 0 ? this.formatPrice(client.options.owes, true):false
        },
        getPhone(client){
          return this.hasOption(client) && client.options.phone !== undefined ? client.options.phone:'---'
        },

        getSkype(client){
          return this.hasOption(client) && client.options.skype !== undefined ? client.options.skype:'---'
        },

        getTimezone(client){
          return this.hasOption(client) && client.options.tz !== undefined ? client.options.tz:'---'
        },

        afterLoaded(response){
            this.$emit('loaded', response)
        },

    }
}   

</script>
