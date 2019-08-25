<template>
    <div class="block-menu" :class="{'active': active, 'inactive': !active}">
        <div class="d-flex justify-content-between expand" @click="active=!active">
            <h2><span v-if="completed" class="dashicons dashicons-yes text-success"></span> {{ title }}</h2>
            <span class="dashicons arrow-flip align-self-center" :class="[active ? 'dashicons-arrow-down-alt2' : 'dashicons-arrow-up-alt2' ]"></span>
        </div>
        <ul :style="getStyle" :ref="'sidemenu-' + title" >
            <li class="m-0" :class="getClass(menu)" v-for="menu in menus">
                <button class="btn btn-sm btn-light btn-block" @click="onClick(menu.route)">
                <span v-if="getClass(menu)" class="dashicons dashicons-yes text-success"></span>
                <span v-else class="dashicons dashicons-minus"></span>
                {{ menu.name }}
                </button></li>
        </ul>
    </div>
</template>

<script>
export default {
    props: ['title', 'menus'],
    data: () => ({
        active: true,
        nextTick: false,
        completed: false
    }),
    mounted(){
        this.nextTick = true
    },
    methods: {
        onClick(route){
            this.$router.push({ name: route})
        },

        getClass(menu){
            return (menu.class!==undefined) ? menu.class :''
        },
        
    },
    computed: {
        getStyle(){
            let style = '';
            let ref = 'sidemenu-' + this.title
            this.nextTick = this.nextTick
            if(this.$refs[ref]!== undefined) {
                style = this.active ? '' : 'margin-bottom: -' + this.$refs[ref].clientHeight + 'px;'
                
            }
            return style
        }
    }
}
</script>
<style>
.block-menu{
    overflow: hidden;
}
.block-menu .btn-block{
    text-align: left;
    padding-left: 30px;
}
.block-menu .expand .dashicons.arrow-flip{
  transition: transform .1s ease-in-out;
}
.block-menu .expand:hover .dashicons.arrow-flip{
  transform: rotate(180deg);
}
.block-menu ul {
    transition: all .3s cubic-bezier(1.0, 0.5, 0.8, 1.0);
    margin-bottom: 0;
}
.block-menu.active  ul {
    
    display: block;
}

.block-menu h2{
    font-size: 1.2rem;
    margin: 0;
}
.block-menu .expand{
    color: #191e23;
    padding: .5rem;
    cursor: pointer;
    border-bottom: 1px solid #ececec;
    border-top: 1px solid #ececec;
}

</style>