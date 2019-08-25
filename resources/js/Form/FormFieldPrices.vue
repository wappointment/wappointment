<template>
    <div>
        <label >{{ label }}</label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text" id="">Price per number of sites</span>
            </div>
            <input type="text" class="form-control" placeholder="number of sites" v-model="number" :class="[validNumber ? 'is-valid':'is-invalid']">
            <input type="text" class="form-control" placeholder="price in € cents (no commas)" v-model="price" :class="[validPrice ? 'is-valid':'is-invalid']">
            <button  class="btn btn-secondary" :class="[isValid ? '':'disabled']" :disabled="!isValid" @click.prevent="addPrice">{{ editMode ? 'Edit':'Add' }}</button>
        </div>
        <div class="mt-2">
            <div class="d-flex align-items-center justify-content-end" :class="{'editing': idp === idx_edit }" v-for="(price, idp) in updatedValue">
                <div>nº of sites: {{ price.number }} = {{ price.price/100 }}€</div> 
                <button class="btn btn-link small" @click.prevent="editPrice(idp)">edit</button>
                <button class="btn btn-link small text-danger" @click.prevent="removePrice(idp)">delete</button>
            </div>
        </div>
    </div>
</template>

<script>
import AbstractField from './AbstractField'
export default {
    mixins: [AbstractField],
    data() {
        return {
            price: undefined,
            number: undefined,
            idx_edit: undefined
        }
    },

    computed: {
        validPrice() {
            return /[0-9]+/.test(this.price) && this.price < 100000 && this.price > 0
        },
        validNumber() {
            return /[0-9]+/.test( this.number) && this.number < 300 && this.number > 0
        },
        isValid(){
            return this.validPrice && this.validNumber
        },
        editMode(){
            return this.idx_edit !== undefined
        }
    },
    methods: {
        
        editPrice(idx){
            this.idx_edit = idx
            this.price = this.updatedValue[this.idx_edit].price
            this.number = this.updatedValue[this.idx_edit].number
        },
        addPrice() {
            if(this.isValid === false) return
            let new_data = {
                    number: this.number,
                    price: this.price,
                }
            if(this.idx_edit !== undefined){
                this.updatedValue[this.idx_edit] = new_data
            }else{
                this.updatedValue.push(new_data)
            }
            
            this.idx_edit = this.price = this.number = undefined
        },
        removePrice(indexpassed) {
            this.updatedValue = this.updatedValue.filter(function(value, index, arr){

                return indexpassed != index;

            });

        }
    }
}
</script>
<style scoped>
.editing{
    background-color:#ccc;
}
</style>

