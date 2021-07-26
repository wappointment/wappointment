<template>
    <div>
        <div v-if="renewAt > 0" >
            <div class="bg-warning text-white p-1 rounded" v-if="days_left < 185"> 
                <div class="d-flex align-items-center justify-content-between">
                    <small> Renew now with <strong>{{ renewAt }}% off</strong> <span class="text-muted">(valid for the next <strong>{{ offer_valid_until }} days</strong>)</span> </small> 
                    <div>
                        <a :href="renewUrl" clasS="btn btn-outline-primary bg-white font-weight-bold" target="_blank" >Renew</a>
                    </div>
                </div>  
            </div>
        </div>
        <div v-else>
            <div class="bg-danger text-white p-1 rounded"> 
                <div class="d-flex align-items-center justify-content-between">
                    <small> Renew now in order to receive new updates</small> 
                    <div>
                        <a :href="renewUrl" clasS="btn btn-outline-primary bg-white font-weight-bold" target="_blank" >Renew</a>
                    </div>
                </div>  
            </div>
        </div>
        
    </div>  
</template>

<script>


export default {
    props: ['days_left', 'price', 'renewUrl'],

    computed: {
        discounted(){
            return (this.price /100 ) *this.renewAt
        },
        licenceKey(){
            return window.licence_key
        },
        offer_valid_until(){
            if(this.renewAt > 40){
                return this.is50
            }
            if(this.renewAt > 30){
                return this.is40
            }
            if(this.renewAt > 20){
                return this.is30
            }
            if(this.renewAt > 10){
                return this.is20
            }
            return this.days_left > 0 ? this.is10:0
        },
        is50(){
            return this.days_left - (30*4)
        },
        is40(){
            return this.days_left - (30*3)
        },
        is30(){
            return this.days_left - (30*2)
        },
        is20(){
            return this.days_left - (30*1)
        },
        is10(){
            return this.days_left - (1)
        },
        renewAt(){
           if(this.is50 > 0 ){
               return 50
           }
           if(this.is40 > 0){
               return 40
           }
           if(this.is30 > 0){
               return 30
           }
           if(this.is20 > 0){
               return 20
           }
           if(this.is10 > 0){
              return 10
           }
           return 0
       }

    },


    methods: {
       convertCentsPrice(price){
           return (price/100).toFixed(2)
       }

    },
}
</script>

