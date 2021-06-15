
<template>
    <div>
        <div class="mb-2">
            <label class="form-check-label" for="active-method">
              <div class="d-flex align-items-center">
                <input type="checkbox" v-model="method.active" id="active-method" @change="activate">
                Enable Method
              </div>
            </label>
            
          </div>
    </div>
</template>

<script>
import abstractView from '../Views/Abstract'
export default {
    extends: abstractView,
    props:['method'],
    methods:{
        activate(){
            this.settingSave('active_methods', this.method.key)
        },
    }
}
</script>

