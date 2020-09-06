<template>
    <div class="wap-head">
        <div v-for="staff in staffs"> 
            <div class="d-flex align-items-center">
                <div class="staff-av" :class="{norefresh: !isStepSlotSelection}" @click="refreshClicked">
                    <img :src="staff.a" :alt="staff.n">
                    <div class="after" v-if="isStepSlotSelection">
                        <svg viewBox="0 0 32 32" class="ic-refresh" aria-hidden="true">
                            <path d="M27.1 14.313V5.396L24.158 8.34c-2.33-2.325-5.033-3.503-8.11-3.503C9.902 4.837 4.901 9.847 4.899 16c.001 6.152 5.003 11.158 11.15 11.16 4.276 0 9.369-2.227 10.836-8.478l.028-.122h-3.23l-.022.068c-1.078 3.242-4.138 5.421-7.613 5.421a8 8 0 0 1-5.691-2.359A7.993 7.993 0 0 1 8 16.001c0-4.438 3.611-8.049 8.05-8.049 2.069 0 3.638.58 5.924 2.573l-3.792 3.789H27.1z"/>
                        </svg>
                    </div>
                </div>
                <div class="staff-desc">
                    <strong>{{ staff.n }}</strong>
                    <div class="header-service" v-if="service!== false && isCompactHeader">
                        {{ service.name }}
                        <span class="wduration">{{duration}}{{getMinText}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import minText from './minText'
export default {
    mixins: [minText],
    props: {
        staffs: {
            type: Array, default: []
        },

        isStepSlotSelection:{
            type: Boolean,
            default: false
        },

        options: {
            type:[Object]
        },
        service: {
            type:[Object, Boolean]
        },
        duration:{
            type: [Boolean,Number]
        },
    },
    data: () => ({
        disabledButtons: false,
    }),
    created(){
        if(this.options.demoData !== undefined){
            this.disabledButtons = true
        }
    },
    methods:{
        refreshClicked(){
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'selection')
              return
            } 
            this.$emit('refreshed')
        }
    },
    computed:{
        isCompactHeader(){
            return this.options.general === undefined || [undefined, false].indexOf(this.options.general.check_header_compact_mode) === -1
        },
    }

}
</script>
<style>
.wap-front .staff-av {
    position:relative;
    cursor: pointer;
}
.wap-front .staff-av .after{
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  height: 100%;
  width: 100%;
  content: "\f463";
  transition: .3s ease;
  background-color: transparent;
  border-radius: 3px;
  opacity:0;
}
.wap-front .staff-av:hover .after{
  background-color: rgba(210, 210, 210, 0.8);
  opacity:1;
}
.wap-front .staff-av .ic-refresh {
  fill: #fff;
}
.wap-front .wap-head .staff-desc {
    padding-left: .4em;
    line-height: 1.2;
    font-size: 1em;
}
.wap-front .staff-av img{
    max-width: 40px;
    display: block;
    overflow: hidden;
    font-size: 12px;
}
.wap-front .staff-av.norefresh {
    cursor: default;
}
</style>

