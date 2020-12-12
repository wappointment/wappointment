<template>
    <div class="country-selector" v-click-outside="clickedOutside" >
        <div v-if="loading" class="d-flex m-auto align-items-center loading-overlay">
            <WLoader />
        </div>
        <label v-if="label" for="search-field">{{ label }}</label>
        <div v-if="selectedCountries.length>0" class="d-flex flex-wrap" >
            
            <div @click="clearSelection" class="btn btn-secondary btn-xs" role="button" >
                <span class="dashicons dashicons-trash"></span> Clear countries
            </div>

            <draggable class="d-flex flex-wrap countryselected" v-model="selectedCountries" @change="$emit('selected',selectedCountries)" draggable=".flg" >
                <div v-for="(iso2, idx) in selectedCountries" v-if="iso2 !== undefined" class="flg m-2" :key="idx+iso2" 
                @mouseover="hovering=iso2" @mouseout="hovering=false" 
                :data-tt="hovering? getCountryName:''" :class="iso2.toLowerCase()">
                    <span @click.prevent="removeCountry(iso2)" class="dashicons dashicons-dismiss"></span>
                </div>
            </draggable>
        </div>
        <div
        class="dropdown"
        @click="toggleDropdown"
        :class="{open: open}"
        >
        <span class="selection d-flex">
            <input ref="search" @keypress="detectKey" id="search-field" class="form-control search flex-fill" :class="{'show':open, 'is-invalid':hasErrors}" placeholder="Select countries" type="text" v-model="search" >
            <span v-if="open" @click.stop="clickedOutside" class="dashicons dashicons-dismiss"></span>
        </span>
            <ul v-show="open" ref="list" @click.stop>

                <li
                class="dropdown-item d-flex align-items-center"
                v-for="(pb, index) in sortedRegions"
                :key="'reg-'+index"
                @click.stop="clickedRegion(pb)"

                @mousemove="selectedIndex = index"
                >
                <strong v-html="getHtmlName(pb)"></strong>
                </li>
                
                
                <li
                class="dropdown-item d-flex align-items-center"
                v-for="(pb, index) in sortedCountries"
                :key="pb.iso2"
                @click.stop="select(pb.iso2)"
                :class="getItemClass(index, pb.iso2)"
                @mousemove="selectedIndex = index"
                >
                <div class="flg mr-2" :class="pb.iso2.toLowerCase()"></div>
                <strong>{{ pb.name }}</strong>
                <span>+{{ pb.dialCode }}</span>
                </li>
            </ul>
        </div>
        <CountryStyle/>
    </div>
</template>

<script>
import allCountries from '../Standalone/all-countries'
import ClickOutside from 'vue-click-outside'
import draggable from 'vuedraggable'
const CountryStyle = () => import(/* webpackChunkName: "style-flag" */ './CountryStyle')
export default {
    props: ['label','selected', 'required', 'hasErrors'],
    components: {CountryStyle, draggable},
    data: () => ({
        countries: allCountries,
        selectedCountries: [],
        open: false,
        selectedIndex: null,
        search: '',
        loading:false,
        regions:[],
        hovering: false
    }),
    created(){
        if(this.selected!== undefined && this.selected.length > 0) this.selectedCountries = [].concat(this.selected)
        this.regions = this.getRegions()
    },
    computed: {
       isSearching(){
           return this.search != ''
       },
       sortedRegions() {
            let search = this.search.trim()
            return search == '' ? this.regions:this.regions.filter(this.searchFilter)
        },
       sortedCountries() {
            let search = this.search.trim()
            return search == '' ? this.countries:this.countries.filter(this.searchFilter)
        },
        getCountryName(){
            if(this.hovering){
                for (let i = 0; i < this.countries.length; i++) {
                    if(this.countries[i].iso2 == this.hovering) {
                        return this.countries[i].name
                    }
                }
            }
        },
    },

    methods: {

        clickedRegion(pb,b){
            this[pb.fn]()
        },
        getHtmlName(pb){
            let html = ''
            if(pb.l !== undefined){
                for (let i = 0; i < pb.l; i++) {
                    html += '&nbsp;&nbsp;'
                }
            }
            return html + pb.name
        },
        getRegions(){
            return [
                {
                    name: 'All World',
                    fn: 'selectAll',
                },

                {
                    l:1,
                    name: 'All Europe',
                    fn: 'selectEurope',
                },
                {
                    l:2,
                    name: 'European Union',
                    fn: 'selectEU',
                },
                {   
                    l:2,
                    name: 'Europe outside EU',
                    fn: 'selectNonEU',
                },
                {
                    l:1,
                    name: 'All America',
                    fn: 'selectAmerica',
                },
                {
                    l:2,
                    name: 'North America',
                    fn: 'selectNAmerica',
                },
                {
                    l:2,
                    name: 'Central America',
                    fn: 'selectCAmerica',
                },
                {
                    l:2,
                    name: 'South America',
                    fn: 'selectSAmerica',
                },
                {
                    l:2,
                    name: 'Caribbean',
                    fn: 'selectCaribbean',
                },

                {
                    l:1,
                    name: 'All Asia',
                    fn: 'selectAsia',
                },
                {
                    l:2,
                    name: 'East Asia',
                    fn: 'selectEastAsia',
                },
                {
                    l:2,
                    name: 'West Asia',
                    fn: 'selectWestAsia',
                },


                {
                    l:1,
                    name: 'All Africa',
                    fn: 'selectAfrica',
                },
                {
                    l:2,
                    name: 'Eastern Africa',
                    fn: 'selectEasternAfrica',
                },
                {
                    l:2,
                    name: 'Central Africa',
                    fn: 'selectCentralAfrica',
                },
                {
                    l:2,
                    name: 'Northern Africa',
                    fn: 'selectNorthernAfrica',
                },
                {
                    l:2,
                    name: 'Southern Africa',
                    fn: 'selectSouthernAfrica',
                },
                {
                    l:2,
                    name: 'Western Africa',
                    fn: 'selectWesternAfrica',
                },

                
                

                {
                    l:1,
                    name: 'Middle East',
                    fn: 'selectMiddleEast',
                },
                {
                    l:1,
                    name: 'Oceania',
                    fn: 'selectOceania',
                },
      
            ]
        },

        clearSelection(){
            this.selectedCountries = []
            this.$emit('selected',this.selectedCountries)
        },
        selectAll(){
            this.loading = true
            setTimeout(this.selectAllDelay, 100);
        },
        selectAllDelay(){
            this.selectedCountries = []
            for (let i = 0; i < this.countries.length; i++) {
                this.selectedCountries.push(this.countries[i].iso2)
            }
            this.$emit('selected',this.selectedCountries)
            this.toggleDropdown()
            this.loading = false
        },
        selectTheseCountries(countries){
            this.loading = true
            setTimeout(this.selectTheseCountriesDelay.bind(null,countries), 100);
        },
        selectTheseCountriesDelay(countries){
            this.selectedCountries = []
            for (let i = 0; i < countries.length; i++) {
                this.selectedCountries.push(countries[i])
            }
            this.$emit('selected',this.selectedCountries)
            this.toggleDropdown()
            this.loading = false
        },
       
        getCountryByRegion(region){
            const countries = {
                'north-africa': ['DZ','EG','LY','MA','SS','SD','TN','EH'],
                'west-africa': ['BJ','BF','CV','CI','GM','GH','GN','GW','LR','MG','ML','MR','NE','NG','ST','SN','SL','TG'],
                'east-africa': ['BI','KM','DJ','ER','ET','KE','MW','MU','YT','MZ','RE','RW','SC','SO','TZ','UG','ZM','ZW'],
                'central-africa': ['AO', 'CM','CF','TD','CD','CG','GQ','GA'],
                'southern-africa': ['BW','LS','NA','ZA','SZ'],
                'west-asia': ['AF','AM', 'AZ','GE','IR','KZ','KG','PK','TJ','UZ'],
                'east-asia': ['BD','BT','IO','BN','KH','CN','HK','IN','ID','JP','LA','MO','MY','MV','MN','MM','NP','KP','PH','SG','KR','LK','TW','TH','TL','TM','VN'],
                'europe-eu': ['AT','BE','CY','HR','BG','CZ','DK','EE','ES','FR','FI','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SE','SK','SI','GB'],
                'europe-non-eu': ['AL','AD', 'BY','BA','FO','GI','GG','IS','IM','JE','XK','LI','MK','MD','MC','ME','NO','RU','SM','RS','SJ','CH','TR','UA','VA','AX'],
                'middle-east': ['BH','IQ','IL','JO','KW','LB','OM','PS','QA','SA','SY','AE','YE'],
                'north-america': ['CA','GL','MX','PR','PM','US'],
                'central-america': ['BZ','CR','SV','GT','HN','NI','PA'],
                'south-america': ['AR','BO','BR','CL', 'CO','EC','FK','GF','GY','PY','PE','SH','SR','UY','VE'],
                'oceania': ['AS', 'AU','CX','CK','FJ','PF','GU','KI','MH','FM','NR','NC','NZ','NU','NF','MP','PW','PG','WS','SB','TK','TO','TV','VU','WF'],
                'caribbean': ['AI', 'AG', 'AW', 'BS', 'BB', 'BM','VG', 'BQ','KY','CU','CW','DM','DO','GD','GP','HT','JM','MQ','MS','BL','KN','LC','MF','VC','SX','TT','TC','VI'],
            }
            return countries[region]
        },
        selectAfrica(){
            this.selectTheseCountries(
                this.getCountryByRegion('north-africa')
                .concat(this.getCountryByRegion('west-africa'))
                .concat(this.getCountryByRegion('east-africa'))
                .concat(this.getCountryByRegion('central-africa'))
                .concat(this.getCountryByRegion('southern-africa'))
            )
        },
         selectNorthernAfrica(){
            this.selectTheseCountries(this.getCountryByRegion('north-africa'))
        },
        selectWesternAfrica(){
            this.selectTheseCountries(this.getCountryByRegion('west-africa'))
        },
        selectEasternAfrica(){
            this.selectTheseCountries(this.getCountryByRegion('east-africa'))
        },
        selectCentralAfrica(){
            this.selectTheseCountries(this.getCountryByRegion('central-africa'))
        },
       
        selectSouthernAfrica(){
            this.selectTheseCountries(this.getCountryByRegion('southern-africa'))
        },
        
        selectAsia(){
            this.selectTheseCountries(
                this.getCountryByRegion('west-asia')
                .concat(this.getCountryByRegion('east-asia'))
                )
        },
        selectWestAsia(){
            this.selectTheseCountries(this.getCountryByRegion('west-asia'))
        },
        selectEastAsia(){
            this.selectTheseCountries(this.getCountryByRegion('east-asia'))
        },
        selectEurope(){
            this.selectTheseCountries(
                this.getCountryByRegion('europe-eu')
                .concat(this.getCountryByRegion('europe-non-eu'))
            )
        },
        selectEU(){
            this.selectTheseCountries(this.getCountryByRegion('europe-eu'))
        },
        selectNonEU(){
            this.selectTheseCountries(this.getCountryByRegion('europe-non-eu'))
        },
        selectMiddleEast(){
            this.selectTheseCountries(this.getCountryByRegion('middle-east'))
        },
        selectAmerica(){
            this.selectTheseCountries(
                this.getCountryByRegion('north-america')
                .concat(this.getCountryByRegion('central-america'))
                .concat(this.getCountryByRegion('south-america'))
            )
        },
        selectNAmerica(){
            this.selectTheseCountries(this.getCountryByRegion('north-america'))
        },
        selectCAmerica(){
            this.selectTheseCountries(this.getCountryByRegion('central-america'))

        },
        selectSAmerica(){
            this.selectTheseCountries(this.getCountryByRegion('south-america'))
        },
        selectOceania(){
            this.selectTheseCountries(this.getCountryByRegion('oceania'))
        },
        selectCaribbean(){
            this.selectTheseCountries(this.getCountryByRegion('caribbean'))
        },
        
        detectKey(e){
            this.open = ([27, 9].indexOf(e.which) !== -1 ) ? false:true

        },
        searchFilter(element) {
            return element.name.toLowerCase().indexOf(this.search.trim()) !== -1
        },
        select(iso2){
            
            let idx = this.selectedCountries.indexOf(iso2)
            if(idx === -1){
                this.selectedCountries.push(iso2)
            }else{
                this.selectedCountries.splice(idx,1)
            }
            this.$emit('selected',this.selectedCountries)
        },
        removeCountry(iso2){
            let idx = this.selectedCountries.indexOf(iso2)
            if(idx !== -1){
                this.selectedCountries.splice(idx,1)
            }
        },
        toggleDropdown() {
            if (this.disabled) {
                return
            }
            this.open = !this.open
            if(this.open === true){
                setTimeout(() => {
                this.$refs.search.focus()
                }, 100)
            }else{
                this.search = ''
            }
        },
        clickedOutside() {
            if(this.open) this.$emit('activated')
            this.open = false
            this.search = ''
            
        },
        getItemClass(index, iso2) {
            const highlighted = this.selectedIndex === index
            const selected = this.selectedCountries.indexOf(iso2) !== -1
            return {
                highlighted,
                selected
            }
        },
    },
    directives: {
        ClickOutside
    }
}
</script>
<style>

.country-selector .flg {
  margin-right: 5px;
  margin-left: 5px;
}
.country-selector .dashicons-dismiss{
    color: #625e5e;
}
.country-selector .flg .dashicons{
    width: 5px;
    height: 5px;
    font-size: 12px;
    position: relative;
    top: -4px;
    left: 12px;
    color: #ad1313;
    cursor: pointer;
}

.country-selector .flg .dashicons::before{
    text-shadow: -1px 0 #fff, 0 1px #fff, 1px 0 #fff, 0 -1px #fff;
}


.country-selector .dropdown-item .flg {
  display: inline-block;
  margin-right: 5px;
  cursor:grab;
}
.country-selector .selection {
  font-size: 0.8em;
  display: flex;
  align-items: center;
}
.country-selector {
  border-radius: 3px;
}

.country-selector .loading-overlay {
    position: absolute;
    height: 100%;
    width: 100%;
    background-color: rgba(0,0,0,.2);
    z-index: 11;
}
.country-selector ul {
  z-index: 1;
  padding: 0;
  margin: 0;
  text-align: left;
  list-style: none;
  max-height: 200px;
  overflow-y: scroll;
  overflow-x: hidden;
  background-color: #fff;
  border: 1px solid #ccc;
  width: 100%;
  box-shadow: 0px 8px 11px 0 rgba(0,0,0,.2);
}
.country-selector .dropdown {
  display: flex;
  flex-direction: column;
  align-content: center;
  justify-content: center;
  position: relative;
  padding: 7px;
  cursor: pointer;
}
.country-selector .dropdown.open {
  background-color: #f3f3f3;
  border-radius: .3rem;
}
.country-selector .dropdown .selection::after {
    display: inline-block;
    width: 0;
    height: 0;
    margin-left: .255em;
    vertical-align: .255em;
    content: "";
    border-top: .3em solid;
    border-right: .3em solid transparent;
    border-bottom: 0;
    border-left: .3em solid transparent;
}
.country-selector .dropdown.open .selection::after {
    display: none;
}
.country-selector .dropdown:hover {
  background-color: #f3f3f3;
}

.country-selector .dropdown-item {
  cursor: pointer;
  padding: 4px 15px;
  border-radius: .3rem;
}
.country-selector .dropdown-item.highlighted {
  background-color: #f3f3f3;
}
.country-selector .dropdown-item.selected {
  background: #6664cb;
    color: #fff;
}

.d-flex.flex-wrap.countryselected {
    background-color: #f4f4f4;
    border: 2px dashed #ccc;
    padding: .4rem;
    margin: .6rem 0;
}
</style>
