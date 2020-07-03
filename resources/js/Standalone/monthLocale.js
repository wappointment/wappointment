import browserLang from "./browserLang"

export default function (monthNumber){
    let objDate = new Date()
    objDate.setDate(1)
    objDate.setMonth(monthNumber)
    let month = objDate.toLocaleString(browserLang(),  { month: "long" })
    
    return month[0].toUpperCase() + month.substring(1)
  }
  
  