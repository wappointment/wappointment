<template>
    <div class="wap-head" :class="{showall:staffSelection}">
        <div class="d-flex" v-if="!staffSelection" :class="[isCompactHeader ? 'align-items-start':'align-items-center']">
            <div class="staff-av" :class="{norefresh: !isStepSlotSelection}" @click="showAllStaff" >
                <div class="avatar-pills">
                    <div v-for="(staffI,i) in staffsFilterd" role="img" :style="getStyleBackground(staffI, i)" :title="staffI.n" class="wstaff-img"></div>
                    <div role="img" :style="getStyleBackground(staff,staffs.length)" :title="staff.n" class="wstaff-img"></div>
                </div>
            </div>
            <div class="staff-desc">
                <div><strong>{{ staff.n }}</strong></div>
                <div class="header-service" v-if="service!== false && isCompactHeader">
                    <span class="compact-servicename">{{ service.name }}</span>
                    <span class="wduration">{{duration}}{{getMinText}}</span>
                </div>
            </div>
        </div>
        <div v-else v-for="staffRow in staffs" > 
            <div class="d-flex" :class="[isCompactHeader ? 'align-items-start':'align-items-center']">
                <div class="staff-av" :class="{norefresh: !isStepSlotSelection}" @click="changeStaff(staffRow)">
                    <div role="img" :style="getStyleBackground(staffRow)" :title="staffRow.n" class="wstaff-img"></div>
                </div>
                <div class="staff-desc">
                    <div><strong>{{ staffRow.n }}</strong></div>
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
        staff:{
            type:[Object]
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
            type: [Boolean,Number, String]
        },
        appointmentSaved: {
            type:Boolean
        },
        rescheduling: {
            type: Boolean
        },
    },
    data: () => ({
        disabledButtons: false,
        staffSelection:false
    }),
    created(){
        if(this.options.demoData !== undefined){
            this.disabledButtons = true
        }
    },
    methods:{
        showAllStaff(){
            this.staffSelection = true
        },
        changeStaff(staff){
            if(this.disabledButtons) {
              this.options.eventsBus.emits('stepChanged', 'selection')
              return
            } 
            this.staffSelection = false
            this.$emit('changeStaff', staff)
        },
        getStyleBackground(staff, i = false){
            return 'background-image: url("'+staff.a+'");' + (i === false?'':'margin-left:0.'+(i*3)+'em;')
        }
    },
    computed:{
        staffsFilterd(){
            let staff = this.staffSelection
            return this.staffs.filter(e => e.id != staff.id)
        },
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
  border-radius: 50%;
  opacity:0;
}
.wap-front .staff-av:hover .after{
  background-color: rgba(210, 210, 210, 0.8);
  opacity:1;
}
.wap-front .staff-av .ic-refresh {
  fill: #fff;
}
.wap-front .wap-head {
    position: absolute;
    width: 100%;
    height: 62px;
    overflow: hidden;
}
.wap-front .wap-head:hover{
    height: auto;
    min-height:62px;
}
.wap-front .wap-head.showall{
    height:100%;
}
.wap-front .wap-head > div {
    padding: 8px;
}


.wap-front .wap-form-body{
    max-height: calc(85vh);
    margin-top:62px;
}

.wap-front .wap-bf.show.has-scroll .wap-head {
    box-shadow: 0px 4px 10px -10px rgba(0,0,0,.9);
}

.wap-front .wap-head .staff-desc {
    padding-left: .4em;
    line-height: 1.2;
    font-size: 1em;
    width: 100%;
}
.wap-front .staff-av img{
    max-width: 46px;
    display: block;
    overflow: hidden;
    font-size: 12px;
}
.wap-front .staff-av.norefresh {
    cursor: default;
}
.wap-front .header-service {
    font-weight: normal;
    font-size:.9em;
}
.wap-front .header-service .wduration{
    font-weight: bold;
    float: right;
}

.staff-av .wstaff-img{
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background-size: cover;
    margin-right: 0;
    box-shadow: -2px 0px 2px 0 rgba(0,0,0,.4);
    border:1px solid #d5d5d5;
}
.compact-servicename{
    max-width: 75%;
    display: inline-block;
}
.avatar-pills{
    display:grid;
}
.avatar-pills .wstaff-img{
    grid-area:1 / 1 / 2 / 2;
}

</style>

