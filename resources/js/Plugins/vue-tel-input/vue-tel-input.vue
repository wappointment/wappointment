<template>
  <div class="phone-field-wrap">
    <div v-click-outside="clickedOutside" class="phone-field" :class="{ disabled: disabled }">
      <div
        :class="[noDrop ? 'nodrop':'dpselect']"
        @click="toggleDropdown"
      >
        <span class="selection d-flex">
          <div class="flg" :class="activeCountry.iso2.toLowerCase()"></div>
        </span>
      </div>
      <LabelMaterial v-if="fieldMaterial" :labelAbove="!fieldMaterial" :forceFloat="phone!=''">
            <input
        :id="id"
        ref="input"
        v-model="phone"
        type="tel"
        class="tel"
        :class="classField"
        :disabled="disabled"
        :required="required"
        :placeholder="hasLabel"
        :autocomplete="autocomplete"
        @blur="onBlur"
        @input="onInput"
      >
      </LabelMaterial>
      <input v-else
        :id="id"
        ref="input"
        v-model="phone"
        type="tel"
        class="tel"
        :class="classField"
        :disabled="disabled"
        :required="required"
        :placeholder="hasLabel"
        :autocomplete="autocomplete"
        @blur="onBlur"
        @input="onInput"
      >

      
    </div>
    <div v-if="open" class="dropdown open">
        <div class="selection d-flex">

          <LabelMaterial v-if="fieldMaterial" :forceFloat="phone!=''">
            <input ref="search" @click.stop class="search flex-fill" :class="{'show':open}" :placeholder="hasLabel" type="text" v-model="search" >
          </LabelMaterial>
          <input v-else ref="search" @click.stop class="search flex-fill" :class="{'show':open}" :placeholder="hasLabel" type="text" v-model="search" >
     </div>
        <ul v-show="open" ref="list">
          <li
            class="d-flex align-items-center"
            v-for="(pb, index) in sortedCountries"
            :key="pb.iso2 + (pb.preferred ? '-preferred' : '')"
            @click="choose(pb)"
            :class="getItemClass(index, pb.iso2)"
            @mousemove="selectedIndex = index"
          >
            <div class="flg" :class="pb.iso2.toLowerCase()"></div>
            <strong>{{ pb.name }}</strong>
            <span>+{{ pb.dialCode }}</span>
          </li>
        </ul>
      </div>
  </div>
</template>
<script>
import ClickOutside from 'vue-click-outside'
import { formatNumber, AsYouType, isValidNumber } from 'libphonenumber-js'
import allCountries from '../../Standalone/all-countries'
import LabelMaterial from '../../Fields/LabelMaterial'
export default {
  props: {
    id: {
      type: String,
    },
    value: {
      type: String,
    },
    classField: {
      type: String,
      default: '',
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    disabledFormatting: {
      type: Boolean,
      default: false,
    },
    invalidMsg: {
      default: '',
      type: String,
    },
    required: {
      type: Boolean,
      default: false,
    },
    defaultCountry: {
      type: String,
      default: '',
    },
    enabledFlags: {
      type: Boolean,
      default: true
    },
    preferredCountries: {
      type: Array,
      default: () => [],
    },
    onlyCountries: {
      type: Array,
      default: () => [],
    },

    autocomplete: {
      type: String,
      default: 'tel',
    },
    hasLabel: {
      type: String,
      default: '',
    },
    fieldMaterial:{
      type: Boolean,
      default: false
    }

  },
  components:{LabelMaterial},
  mounted() {
    this.initializeCountry()
    this.$emit('onValidate', this.response)
  },
  created() {
    if (this.value) {
      this.phone = this.value
    }
    //this.baseCountryList = allCountries

    if (this.onlyCountries.length > 0) {
      for (let i = 0; i < this.onlyCountries.length; i++) {
        const iso2 = this.onlyCountries[i]
        this.baseCountryList.push(allCountries.find(e => e.iso2 == iso2))
      }

    }else{
      this.baseCountryList = allCountries
    }
  },
  data() {
    return {
      phone: '',
      activeCountry: { iso2: '' },
      open: false,
      selectedIndex: null,
      search: '',
      baseCountryList: []
    }
  },
  watch: {
    state(value) {
      if (value && this.mode !== 'prefix') {
        // If mode is 'prefix', keep the number as user typed,
        // Otherwise format it
        this.phone = this.formattedResult
      }
      this.$emit('onValidate', this.response)
    },
    value() {
      this.phone = this.value
    },
  },
  computed: {
    labelClass () {
      var obj = {
        'label-wrapper': true,
        'focused': this.isFocused,
        'active': this.isActive || this.forceFloat,
      }
      if(this.extraClass != undefined) obj[this.extraClass] = true
      return obj
    },
    noDrop(){
      return this.oneCountryOnly || this.mode === 'code'
    },
    oneCountryOnly(){
      return this.onlyCountries.length === 1
    },
    mode() {
      if (!this.phone) {
        return ''
      }
      if (this.phone[0] === '+') {
        return 'code'
      }
      if (this.phone[0] === '0') {
        return 'prefix'
      }
      return 'normal'
    },
    
    filteredCountries() {
      // List countries after filtered
      

      return this.baseCountryList
    },
    
    
    formattedResult() {
      // Calculate phone number based on mode
      if (!this.mode || !this.filteredCountries) {
        return ''
      }
      let phone = this.phone
      if (this.mode === 'code') {
        // If user manually type the country code
        const formatter = new AsYouType()
        formatter.input(this.phone)

        // Find inputted country in the countries list
        this.activeCountry = this.findCountry(formatter.country) || this.activeCountry
      } else if (this.mode === 'prefix') {
        // Remove the first '0' if this is a '0' prefix number
        // Ex: 0432421999
        if(this.activeCountry.doNotSlice.length < 1 || this.phone.substr(0,2).indexOf(this.activeCountry.doNotSlice) === -1){
          phone = this.phone.slice(1)
        }
        
      }
      if (this.disabledFormatting) {
        return this.phone
      }

      return formatNumber(phone, this.activeCountry && this.activeCountry.iso2, 'International')
    },
    state() {
      
      let isValid = isValidNumber(this.formattedResult, this.activeCountry.iso2/*  && this.activeCountry.iso2 */)
      if(isValid) this.phone = this.formattedResult
      return isValid
    },
    response() {
      // If it is a valid number, returns the formatted value
      // Otherwise returns what it is
      const response = {
        number: this.state ? this.formattedResult : this.phone,
        isValid: this.state,
        country: this.activeCountry,
      }
      // If formatting to the input is disabled, try to return the formatted value to its parent
      if (this.disabledFormatting) {
        Object.assign(response, {
          formattedNumber: formatNumber(this.phone, this.activeCountry && this.activeCountry.iso2, 'International')
        })
      }
      return response
    },

    sortedCountries() {
      // Sort the list countries: from preferred countries to all countries
      const preferredCountries = this.getCountries(this.preferredCountries)
        .map(country => ({ ...country, preferred: true }))

      let countrylist = [...preferredCountries, ...this.filteredCountries]
      let search = this.search.trim()
      if(search!=''){
        return countrylist.filter(this.searchFilter)
      }
      return countrylist
    },
  },

  methods: {
    filterOnlyCountries ({ iso2 }) {
      return this.onlyCountries.includes(iso2.toUpperCase())
    },
    searchFilter(element) {
      return element.name.toLowerCase().startsWith(this.search.trim().toLowerCase())
    },
    initializeCountry() {
      /**
       * 1. Use default country if passed from parent
       */
      if (this.defaultCountry) {
        const defaultCountry = this.findCountry(this.defaultCountry)
        if (defaultCountry) {
          this.activeCountry = defaultCountry
          return
        }
      }
      /**
       * 2. Use the first country from preferred list (if available) or all countries list
       */
      this.activeCountry = this.findCountry(this.preferredCountries[0]) || this.filteredCountries[0]

    },
    /**
     * Get the list of countries from the list of iso2 code
     */
    getCountries(list = []) {
      return list
        .map(countryCode => this.findCountry(countryCode))
        .filter(Boolean)
    },
    findCountry(iso = '') {
      return this.filteredCountries.find(country => country.iso2 === iso.toUpperCase())
    },
    getItemClass(index, iso2) {
      const hl = this.selectedIndex === index
      const preferred = !!~this.preferredCountries.map(c => c.toUpperCase()).indexOf(iso2)
      return {
        hl,
        preferred,
      }
    },
    choose(country) {
      this.activeCountry = country
      this.$emit('onInput', this.response)
    },
    onInput() {
      this.$refs.input.setCustomValidity(this.response.isValid ? '' : this.invalidMsg)
      // Emit input event in case v-model is used in the parent
      this.$emit('input', this.response.number)

      // Emit the response, includes phone, validity and country
      this.$emit('onInput', this.response)
    },
    onBlur() {
      this.$emit('onBlur')
    },
    toggleDropdown() {
      if (this.disabled || this.noDrop) {
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
      this.open = false
    },


    reset() {
      this.selectedIndex = this.sortedCountries.map(c => c.iso2).indexOf(this.activeCountry.iso2)
      this.open = false
    },
  },
  directives: {
        ClickOutside
    }
}
</script>
<style>
.phone-field-wrap .flg {
  margin-right: .4em;
  margin-left: .4em;
}
.phone-field-wrap .dropdown li .flg {
  display: inline-block;
  margin-right: .4em;
}
.phone-field .selection {
  font-size: 0.8em;
  display: flex;
  align-items: center;
}
.phone-field {
  border-radius: .25em;
  display: flex;
  border: 1px solid #bbb;
  text-align: left;
}
.phone-field-wrap .label-wrapper{
  margin-bottom: 0 !important;
}

.wap-front .phone-field-wrap .phone-field input.tel {
  border: none;
  border-radius: 0 .25em .25em 0;
  width: 100%;
  outline: none;
  padding-left: .5em;
  min-width: 100px;
  color: var(--wappo-input-col);
  background: #fff;
}

html[dir=rtl] .wap-front .phone-field-wrap .phone-field input.tel {
  direction: ltr;
  text-align: right;
}

.phone-field-wrap ul {
  padding: 0;
  margin: 0;
  list-style: none;
  overflow-y:scroll;
  max-height: 200px;
  background-color: #fff;
}
.phone-field-wrap .dpselect, .phone-field-wrap .nodrop  {
  display: flex;
  flex-direction: column;
  align-content: center;
  justify-content: center;
  position: relative;
  padding: .5em;
  border-radius: .25em 0 0 .25em;
  border-right: 1px solid #eaeaea;
  background:#f9f9f9;
}
.phone-field-wrap .dpselect {
  cursor: pointer;
}
.phone-field .dpselect:hover {
  background: #f3f3f3;
}

.phone-field-wrap .dpselect .selection::after {
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



.phone-field-wrap .dropdown li {
  cursor: pointer;
  padding: .3em;
  font-size: .8em;
}
.phone-field-wrap .dropdown li.hl {
  background-color: #f3f3f3;
}
.phone-field.disabled .selection,
.phone-field.disabled .dropdown,
.phone-field.disabled input.tel {
  cursor: no-drop;
}
.phone-field-wrap .search{
  display:none;
}
.phone-field-wrap .search.show{
  display: block;
  border: 0 !important;
  height: calc(2.25em + 2px);
  width: auto;
}
.phone-field-wrap .dropdown.open{
    background: #fff;
    padding: .3em;
    z-index: 99;
    border: 1px solid #eaeaea;
    border-radius: .25em;
    box-shadow: 0px 8px 10px 0 rgba(0,0,0,.08);
}

.br-fixed .phone-field-wrap .dropdown.open {
    position: relative;
}
.phone-field-wrap .dropdown.open li {
    margin: .5rem 0;
}
</style>