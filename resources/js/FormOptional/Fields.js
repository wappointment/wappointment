//cannot split chunks here
import FieldTipTap from './FieldTipTap'
import FieldImageSelect from './FieldImageSelect'
import FieldTimezone from './FieldTimezone'
import FieldPhone from './FieldPhone'
import FieldModality from './FieldModality'
import FieldMultiDuration from './FieldMultiDuration'
import FieldCustomField from './FieldCustomField'
window.wappointmentExtends.add('FormGeneratorFields', function (allComponents, extraComponents) {
    allComponents['FieldTipTap'] = FieldTipTap
    allComponents['FieldImageSelect'] = FieldImageSelect
    allComponents['FieldTimezone'] = FieldTimezone
    allComponents['FieldPhone'] = FieldPhone
    allComponents['FieldModality'] = FieldModality
    allComponents['FieldMultiDuration'] = FieldMultiDuration
    allComponents['FieldCustomField'] = FieldCustomField
    return allComponents
})


