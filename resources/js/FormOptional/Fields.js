/* import BillServices from './BillServices'
import FieldPrices from './FieldPrices'
import FieldSolutions from './FieldSolutions' */
import FieldTipTap from './FieldTipTap'
import FieldImageSelect from './FieldImageSelect'

window.wappointmentExtends.add('FormGeneratorFields', function (allComponents, extraComponents) {
/*     allComponents['BillServices'] = BillServices
    allComponents['FieldPrices'] = FieldPrices
    allComponents['FieldSolutions'] = FieldSolutions */
    allComponents['FieldTipTap'] = FieldTipTap
    allComponents['FieldImageSelect'] = FieldImageSelect
    return allComponents
})


