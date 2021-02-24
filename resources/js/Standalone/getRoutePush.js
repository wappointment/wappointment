export default function routePush(hash = false) {
  let rightHash = hash === false ? window.location.hash:hash
  let params_array = rightHash.replace('#/','').replace('/','_').split('/')
  let pushtorout = { name: params_array[0]}
  if(params_array.length>1){
    pushtorout.params = {id:params_array[1]}
  }
  return pushtorout
}
