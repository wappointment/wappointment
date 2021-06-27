<template>
    <div class="wap-head">
        <div v-if="staffSelected" class="d-flex" :class="getClassWrapper">
            <div class="staff-av" role="button" @click="showAllStaff" >
                <div :data-tt="canChangeStaff ? 'Change Staff':''">
                    <div role="img" :style="getStyleBackground(staff)" :title="staff.n" class="wstaff-img"></div>
                </div>
            </div>
            <div class="staff-desc" v-if="showRightText">
                <div role="button" @click="showAllStaff" v-if="showStaffName" :class="{'wcan-edit':canChangeStaff}"><strong>{{ staff.n }}</strong></div>
                <div class="header-service" v-if="service!== false && isCompactHeader">
                    <span class="compact-servicename" role="button" :class="{'wcan-edit':canChangeService}" @click="changeService">{{ service.name }}</span>
                    <span v-if="duration" role="button" class="wduration" :class="{'wcan-edit':canChangeDuration}" @click="changeDuration">{{duration}}{{getMinText}}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import minText from './minText'
import MixinChange from './MixinChange'
import IsDemo from '../Mixins/IsDemo'
export default {
    mixins: [minText, window.wappointmentExtends.filter('MixinChange', MixinChange), IsDemo],
    props: {
        staffs: {
            type: Array
        },
        services: {
            type: Array
        },
        staff:{
            type:Object
        },
        options: {
            type:Object
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
        attributesEl: {
            type: Object
        },
    },
    methods:{
        
        showAllStaff(){
            if(this.disabledButtons) {
              return
            } 
            if(this.canChangeStaff){
                return this.$emit('showStaffScreen', 'BookingStaffSelection',{ selectedStaff:null, selectedSlot:false, service: false, location: false, duration: false,})
            }
        },
        getStyleBackground(staff){
            return 'background-image: url("'+staff.a+'");'
        }
    },
    computed:{
        getClassWrapper(){
            return [
                this.itemsCenterVertically ? 'align-items-center':'align-items-start',
                this.showRightText ? '':'justify-content-center',
            ]
        },
        showRightText(){
            return (this.isCompactHeader && (this.service || this.showStaffName )) || (!this.isCompactHeader && this.showStaffName)
        },
        itemsCenterVertically(){
            return !this.isCompactHeader || (this.isCompactHeader && !this.showStaffName)
        },
        staffSelected(){
            return this.staff !== null
        },
        staffsFilterd(){
            return this.canChangeStaff && this.staffSelected ? [this.staff]:[]
        },
        hasNoGeneralOptions(){
            return this.options.general === undefined
        },
        isCompactHeader(){
            return this.hasNoGeneralOptions || [undefined, false].indexOf(this.options.general.check_header_compact_mode) === -1
        },
        showStaffName(){
            return this.hasNoGeneralOptions || [undefined, false].indexOf(this.options.general.check_hide_staff_name) !== -1
        },
    }

}
</script>
<style>
.wap-front .staff-av {
    position:relative;
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
    border:2px solid #eaeaea;
    background-color:#fff;
}

.selectable-staff{
    cursor: pointer;
}
.compact-servicename{
    max-width: 75%;
    display: inline-block;
}
.wcan-edit{
    cursor: pointer;
    text-decoration: underline;
}

</style>

