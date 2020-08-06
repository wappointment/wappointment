import FormFieldInput from './FormFieldInput'
import FormFieldInputs from './FormFieldInputs'
import FormFieldCheckbox from './FormFieldCheckbox'
import FormFieldDuration from './FormFieldDuration'
import FormFieldAddress from './FormFieldAddress'
import FormFieldFile from './FormFieldFile'
import FormFieldUpload from './FormFieldUpload'
import FormFieldEditor from './FormFieldEditor'
import FormFieldStatus from './FormFieldStatus'
import FormFieldCountrySelector from './FormFieldCountrySelector'
import FormFieldSelect from './FormFieldSelect'
import FormFieldCheckImages from './FormFieldCheckImages'
import FormFieldDate from './FormFieldDate'
import FormFieldTextarea from './FormFieldTextarea'
import FormFieldLabel from './FormFieldLabel'

// components shared
import AbstractField from './AbstractField'
import RequestMaker from '../Modules/RequestMaker'

let allComponents = window.wappointmentExtends.filter( 'FormGeneratorFields', {FormFieldInput,FormFieldInputs, FormFieldCheckbox, FormFieldEditor,
    FormFieldStatus,FormFieldFile, FormFieldSelect,FormFieldCheckImages,
    FormFieldAddress, FormFieldDuration,FormFieldCountrySelector, FormFieldLabel, FormFieldUpload, FormFieldDate,FormFieldTextarea},
    { AbstractField, RequestMaker } )
let inputTypes = {}

for (const key in allComponents) {
    if (allComponents.hasOwnProperty(key)) {
        inputTypes[allComponents[key].name] = key
    }
}

export default {
    components: allComponents,
    inputTypes: inputTypes,
    mightUse: { AbstractField, RequestMaker }
}


