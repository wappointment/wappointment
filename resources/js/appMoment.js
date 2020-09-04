//let momenttz = require ('moment-timezone') 
let momenttz = null
const myPromise = import(/* webpackChunkName: "wappo-moment" */ 'moment-timezone/builds/moment-timezone-with-data-2012-2022.js')

Promise.resolve(myPromise).then(function(value){
    momenttz = value
})


export default momenttz