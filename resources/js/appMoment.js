//let momenttz = require ('moment-timezone') 
let momenttz = require ('moment-timezone/builds/moment-timezone-with-data-2012-2022.js') 

window.waplocale = 'en'
momenttz.locale(window.waplocale)
momenttz.tz.setDefault()

export default momenttz