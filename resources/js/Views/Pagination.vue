<template>
    <div v-if="canShow">
        <nav aria-label="...">
            <ul class="pagination pagination-sm">
                <li v-for="i in last_page" class="page-item" :class="{disabled: isCurrent(i)}">
                    <a class="page-link" href="javascript:;" @click="changePage(i)" tabindex="-1">{{ i }}</a>
                </li>
            </ul>
        </nav>
    </div>  
</template>

<script>


export default {
    props:['pagination'],
    computed: {
        last_page(){
            return this.pagination.last_page
        },
        canShow(){
            return this.pagination.total > 1
        },
    },
    methods: {
        isCurrent(page){
            return this.pagination.current_page === page
        },
        changePage(page){
            return this.isCurrent(page) ? false:this.$emit('changePage', page)
        }
    },

}
</script>