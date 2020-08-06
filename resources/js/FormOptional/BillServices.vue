<template>
    <div>
        <div class="d-flex" v-for="(element, idElement) in updatedValue">
            <LabelMaterial>
                <input type="text" class="form-control" placeholder="Service" 
                v-model="element.name">
            </LabelMaterial>
            <LabelMaterial>
                <input   
                type="text" class="form-control" placeholder="Price" 
                v-model="element.price">
            </LabelMaterial>
            <button @click.prevent.stop="deleteElement(idElement)">Delete</button>
        </div>
        <button class="btn btn-secondary" @click.prevent.stop="addLine">Add Service</button>
    </div>
</template>

<script>
import AbstractField from '../Form/AbstractField'
import LabelMaterial from '../Fields/LabelMaterial'
export default {
    name:'opt-bill-services',
    components: {LabelMaterial},
    mixins: [AbstractField],
    data(){
        return {
            updatedValue: [{name:'', price:''}]
        }
    },
    methods:{
        addLine(){
            this.updatedValue.push({name:'', price:''})
        },
        deleteElement(id){
            this.updatedValue = this.updatedValue.filter((el, index) => index != id)
        },
    },

}
</script>

