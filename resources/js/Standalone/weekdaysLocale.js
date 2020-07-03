import browserLang from "./browserLang"

export default function (){

  let tomorrow = new Date()
  let days = []
  for (let i = 0; i < 7; i++) {
    days[i] = {
      en: tomorrow.toLocaleDateString('en-US', { weekday: 'long'}), 
      locale: new Intl.DateTimeFormat(browserLang(), { weekday: 'long'}).format(tomorrow)
    }
    tomorrow.setDate(tomorrow.getDate() + 1)
  }
  return days
}
