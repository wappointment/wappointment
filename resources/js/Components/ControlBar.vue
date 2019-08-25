<template>
    <nav v-if="hasMenus" id="sidebar" :class="[active ? 'expanded' : 'collapsed' ]">
        <div class="d-flex">
            <div class="collapsebtn" @click="active=!active">
                <span class="dashicons" :class="[active ? 'dashicons-arrow-left-alt2' : 'dashicons-arrow-right-alt2' ]"></span>
            </div>
            <div class="items" >
                <WizardMenu v-if="initialSetupPending" title="Initial setup" :menus="wizardMenu" :progress="progressWizard"></WizardMenu>
            </div>
        </div>
    </nav>
</template>

<script>
import WizardMenu from './WizardMenu'
import BlockMenu from './BlockMenu'
export default {
    components: {
        BlockMenu,
        WizardMenu
    }, 
    props: ['progressWizard'],
    data: () => ({
        active: true,
        wizardMenu: [
            {
                name: 'Weekly availability',
                route: 'wizard2'
            },
            {
                name: 'Service setup',
                route: 'wizard3'
            },
            {
                name: 'Booking widget',
                route: 'wizard4'
            }
        ],
    }),
    computed: {
        initialSetupPending(){
            return this.progressWizard < 4
        },
        hasMenus(){
            return this.initialSetupPending
        }
    },

}
</script>
<style>
#sidebar {
    position: fixed;
    top: 82px;
    right: 0;
    height: 100vh;
    z-index: 29;
    transition: right .3s cubic-bezier(0.755, 0.050, 0.855, 0.060);
}

#sidebar.collapsed{
    right: -250px;
}

#sidebar > .d-flex {
    height: 100%
}

#sidebar .collapsebtn{
    background-image: linear-gradient(to right, transparent, rgba(0,0,0,.1));
    width:1rem;
    cursor:pointer;
}
#sidebar .collapsebtn .dashicons{
    position: absolute;
    top: 50%;
}
#sidebar .items{
    width: 250px;
    background-color: rgba(255,255,255,.9);
    box-shadow: 0 .01rem .6rem 0 rgba(0,0,0,.1);
    border-left: 1px solid #ececec;
}
#sidebar .collapsebtn .dashicons{
  transition: all .1s ease-in-out;
}
#sidebar .collapsebtn:hover .dashicons{
  transform: rotate(180deg);
}

@media (max-width: 470px) { 
    #sidebar {
        top: 106px;
    }
}

</style>