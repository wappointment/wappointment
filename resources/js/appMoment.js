let momenttz = require ('moment-timezone') 
window.waplocale = 'en'
momenttz.locale(window.waplocale)
momenttz.tz.setDefault()

export default momenttz