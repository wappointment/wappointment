let replacements = {
    'd' : 'DD', // 01 til 31 Day of month
    'D' : 'ddd', // Mon til Sun
    'j' : 'D', // 1 til 31	Day of month
    'l' : 'dddd', // Sunday through Saturday
    'N' : 'E', // 1 (for Monday) through 7 (for Sunday)
    'S' : 'o', // st, nd, rd or th. Works well with j
    'w' : 'e', // 0 (for Sunday) through 6 (for Saturday)
    'z' : 'DDD', // 0 through 365
    'W' : 'W', // week in the year e.g. 42
    'F' : 'MMMM', // January through December
    'm' : 'MM', // 01 through 12
    'M' : 'MMM', // Jan through Dec
    'n' : 'M', // 1 through 12
    't' : '', // 28 through 31 number of days in a given month
    'L' : '', // is a leap year 1 or 0
    'o' : 'YYYY', // 2014
    'Y' : 'YYYY', // 2014
    'y' : 'YY', // 14
    'a' : 'a', // am or pm
    'A' : 'A',// AM or PM
    'B' : '', // 000 through 999 Swatch Internet time
    'g' : 'h', //1..12	Hours (12 hour time used with a A.)
    'G' : 'H', //0..23	Hours (24 hour time)
    'h' : 'hh', //01..12	Hours (12 hour time used with a A.)
    'H' : 'HH', //00..23	Hours (24 hour time)
    'i' : 'mm', // 00 to 59 min
    's' : 'ss', //00 through 59 sec
    'u' : 'SSS', // micros sec 654321
    'v' : '', // milliseconds
    'e' : 'zz',  // timezone
    'I' : '', // daylight saving time 1 or 0
    'O' : '', // diff to GMT +0200
    'P' : '', // diff to GMT +0200
    'T' : '', // timezone abbrv EST MDT, etc...
    'Z' : '', // timezone offset in seconds
    'c' : '', // full: 2004-02-12T15:19:21+00:00
    'r' : '', // full: Thu, 21 Dec 2000 16:01:07 +0200
    'U' : 'X', // unix timestamp
}

export default function(format){
    
    let keysObj = Object.keys(replacements)

    for (let index = 0; index < keysObj.length; index++) {
        if(keysObj[index]!==undefined)format = format.replace('\\'+keysObj[index], '['+keysObj[index]+']')
    }

    let newFormat = '';

    let ignoreUntilClosingBracket = false;
    for (var i = 0; i < format.length; i++) {
        if(format[i] == '[') {
            ignoreUntilClosingBracket = true
        }

        if(replacements[format[i]] === undefined || ignoreUntilClosingBracket){
            newFormat += format[i]
        } 
        else{
            newFormat += replacements[format[i]]
        } 

        if(format[i] == ']') {
            ignoreUntilClosingBracket = false
        }
    }
    return newFormat
}
  
  