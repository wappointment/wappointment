export default function (router){
  let wizardInt = parseInt(wappointmentAdmin.wizardStep)
  if(wizardInt > -1){
      switch (wizardInt) {
          case 0:
          case 1:
          case 2:
          case 3:
              router.push({ name: 'wizard'+(wizardInt+1)})
              return true
      }
      
  }
  return false
}
