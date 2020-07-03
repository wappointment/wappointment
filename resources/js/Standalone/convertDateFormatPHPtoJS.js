import replaceBulk from "./replaceBulk"

export default function(format){
    let replacements = {
        'd' : {day: '2-digit'}, // 01 til 31 Day of month
        'D' : {weekday: 'short'}, // Mon til Sun
        'j' : {day: 'numeric'}, // 1 til 31	Day of month
        'l' : {weekday: 'long'}, // Sunday through Saturday
        'N' : {weekday: 'short'}, // 1 (for Monday) through 7 (for Sunday)
        'S' : '', // st, nd, rd or th. Works well with j
        'w' : {weekday: 'short'}, // 0 (for Sunday) through 6 (for Saturday)
        'z' : '', // 0 through 365
        'W' : '', // week in the year e.g. 42
        'F' : {month: 'long'}, // January through December
        'm' : {month: '2-digit'}, // 01 through 12
        'M' : {month: 'short'}, // Jan through Dec
        'n' : {month: 'numeric'}, // 1 through 12
        't' : '', // 28 through 31 number of days in a given month
        'L' : '', // is a leap year 1 or 0
        'o' : {year: 'numeric'}, // 2014
        'Y' : {year: 'numeric'}, // 2014
        'y' : {year: '2-digit'}, // 14
        'a' : {ampm: true}, // am or pm
        'A' : {ampm: true}, // AM or PM
        'B' : '', // 000 through 999 Swatch Internet time
        'g' : {hour: 'numeric', hour12:true}, //1..12	Hours (12 hour time used with a A.)
        'G' : {hour: '2-digit'}, //0..23	Hours (24 hour time)
        'h' : {hour: 'numeric', hour12:true},//01..12	Hours (12 hour time used with a A.)
        'H' : {hour: '2-digit'},//00..23	Hours (24 hour time)
        'i' : {minute: '2-digit'}, // 00 to 59 min
        's' : {second: '2-digit'}, //00 through 59 sec
        'u' : '', // micros sec 654321
        'v' : '', // milliseconds
        'e' : {timeZoneName: 'long'}, // timezone
        'I' : '', // daylight saving time 1 or 0
        'O' : '', // diff to GMT +0200
        'P' : '', // diff to GMT +02:00
        'T' : {timeZoneName: 'short'}, // timezone abbrv EST MDT, etc...
        'Z' : '', // timezone offset in seconds
        'c' : '', // full: 2004-02-12T15:19:21+00:00
        'r' : '', // full: Thu, 21 Dec 2000 16:01:07 +0200
        'U' : '', // unix timestamp
    };
    
    let keysObj = Object.keys(replacements)
    let new_format_pattern = format 
    let new_format_object = {}

    let pattern_replace = []
    for (let i = 0; i < keysObj.length; i++) {
        let stringg = Object.keys(replacements[keysObj[i]])[0]
        pattern_replace[i] = stringg === undefined? '':'\{'+stringg+'\}'
    }

    new_format_pattern = replaceBulk( format, keysObj, pattern_replace )

    for (let i = 0; i < keysObj.length; i++) {

        if(keysObj[i]!==undefined){
            let found = format.indexOf(keysObj[i])
            
            if(found !== -1){
                new_format_object = Object.assign(new_format_object, replacements[keysObj[i]])                    
            }
        }
    }
    
    return new_format_object
}
  
  