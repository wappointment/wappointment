export default function routePush(hash = false, to = false) {
  let rightHash = hash === false ? window.location.hash:hash
  let params_array = rightHash.replace('#/','').replace('/','_').split('/')
  let pushtorout = { name: params_array[0]}
  
  if(params_array.length>1){
    pushtorout.params = {id:params_array[1]}
  }else{
    if(params_array[0] == ''){
      pushtorout =  to !== false ? {name: to.query.page}: false
    } 
    if(params_array[0].charAt(params_array[0].length-1) == '_'){
      pushtorout =  {name: params_array[0].replace('_', '')}
    }
  }
  console.log('pushtorout',pushtorout)
  return pushtorout
}
