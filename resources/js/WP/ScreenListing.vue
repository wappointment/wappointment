<template>
    <WPScreenOptions>
        <div class="d-flex mb-2">
            <label class="form-check-label w-100" for="per_page">
            <div class="d-flex align-items-center">
                <label for="per_page" class="col-sm-3">Results per page</label>
                <div class="col-sm-4">
                    <select class="form-control" id="per_page" 
                    @change="changedPP" v-model="per_page_m">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            </label>
        </div>
    </WPScreenOptions>
</template>

<script>
import WPScreenOptions from '../WP/ScreenOptions'
export default {
    props:{
        per_page:{
            type: Number,
            default: 10
        }
    },
    data() {
        return {
            per_page_m: 10,
        } 
    },
    components: {WPScreenOptions},
    created(){
        this.per_page_m = this.per_page
    },
    methods:{
        changedPP(){
            this.$emit('perPage', this.per_page_m)
        }
    }
}
</script>