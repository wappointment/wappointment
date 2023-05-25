
<template>
    <div>
        <div v-if="clientSelected">
            <div class="d-flex align-items-center">
                <div class="mr-2">
                    <img class="rounded-circle" :src="clientSelected.avatar" :title="clientSelected.name" />
                </div>
                <div>
                    <h6 class="m-0">{{ clientSelected.name }}</h6>
                    <small>{{ clientSelected.email }}</small>
                </div>
            </div>
            <a class="text-primary" href="javascript:;" @click="clearClientSelection">Change client</a>
        </div>
        <div v-else>
            <div class="mb-3">
                <div>
                    <div class="field-required ddd " :class="hasError()">
                        <label for="bookingemail">Email:</label> 
                        <p class="d-flex">
                            <input id="bookingemail" type="text" required="required" class="form-control" 
                            v-model="email" @focus="canShowDropdown" @blur="clearDropdownDelay">
                        </p>
                    </div> 
                </div>

                <div>
                    <div class="dd-search-results" v-if="showDropdown" >
                        <div v-if="clientsResults.length>0">
                            <div class="btn btn-light d-flex align-items-center" v-for="client in clientsResults" @click="selectClient(client)">
                                <div class="mr-2">
                                    <img class="rounded-circle" :src="client.avatar" :title="client.name">
                                </div>
                                <div>
                                    <h6 class="m-0 text-left">{{ client.name }}</h6>
                                    <small>{{ client.email }}</small>
                                </div>
                            </div>
                        </div>
                        <div v-if="clientSearching">
                            Loading ...
                        </div>
                    </div>  
                </div>
            </div>
            
        </div>
    </div>
</template>

<script>
import ClientService from '../Services/V1/Client'
import isEmail from 'validator/es/lib/isEmail'
import isEmpty from 'validator/es/lib/isEmpty'
export default {
    data: () => ({
        clientsResults: [],
        clientSearching:false,
        clientSelected:false,
        email:'',
        clientid: false,
        serviceClient: null,
        showDropdown: false,
    }),
    created(){
        this.serviceClient = this.$vueService(new ClientService)
    },
    watch: {
      email: {
          handler: function(newValue, oldvalue){
            if(newValue!== undefined && newValue.length > 4 
            && newValue.indexOf('@')!== -1 && oldvalue != newValue){
                this.searchClient(newValue)
            }
            this.$emit('input', newValue)
          },
          deep: true
      },

    },
    computed:{
        emailIsValid(){
            return this.email!== undefined && isEmpty(this.email) || !isEmail(this.email)
        }
    },
    methods:{
        hasError(){
            return this.emailIsValid ? 'isInvalid':'isValid'
        },
        selectClient(client){
            this.clientid = client.id
            this.clientSelected = client
            this.email = ''
            this.showDropDown = false
            this.$emit('selectedClient', client)
        },
        clearClientSelection(){
            this.clientid = false
            this.clientSelected = false
            this.$emit('selectedClient', false)
        },

        canShowDropdown(){
            if(this.clientsResults.length > 0){
                this.showDropdown = true
            }
        },
        clearDropdownDelay(){
            setTimeout(this.clearDropDown, 100)
        },
        clearDropDown(){
            this.showDropdown = false
        },

        searchClient(){
            if(!this.clientSearching){
                this.clientSearching = true
                this.showDropdown = true
                this.clientsResults = []
                this.searchClientRequest(this.email).then(
                function(result){
                    return this.clientsFound(result)
                }.bind(this),
                function(err){
                    return this.clientsError(err)
                }.bind(this))
            }
        },
        async searchClientRequest(email) {
            return await this.serviceClient.call('search', {email: email})
        },
        clientsFound(result){
            this.clientSearching = false
            if(result.data!== undefined && result.data.length > 0){
                this.clientsResults = result.data
            }
            
        },
        clientsError(){
            this.clientSearching = false
        },
       
    }
}
</script>
