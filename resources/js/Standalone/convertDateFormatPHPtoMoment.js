export default function(format){
    let replacements = {
        'd' : 'DD',
        'D' : 'ddd',
        'j' : 'D',
        'l' : 'dddd',
        'N' : 'E',
        'S' : 'o',
        'w' : 'e',
        'z' : 'DDD',
        'W' : 'W',
        'F' : 'MMMM',
        'm' : 'MM',
        'M' : 'MMM',
        'n' : 'M',
        't' : '', // no equivalent
        'L' : '', // no equivalent
        'o' : 'YYYY',
        'Y' : 'YYYY',
        'y' : 'YY',
        'a' : 'a',
        'A' : 'A',
        'B' : '', // no equivalent
        'g' : 'h',
        'G' : 'H',
        'h' : 'hh',
        'H' : 'HH',
        'i' : 'mm',
        's' : 'ss',
        'u' : 'SSS',
        'e' : 'zz', // deprecated since version 1.6.0 of moment.js
        'I' : '', // no equivalent
        'O' : '', // no equivalent
        'P' : '', // no equivalent
        'T' : '', // no equivalent
        'Z' : '', // no equivalent
        'c' : '', // no equivalent
        'r' : '', // no equivalent
        'U' : 'X',
    };
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
  
  