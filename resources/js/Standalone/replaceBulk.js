export default function replaceBulk( str, findArray, replaceArray ){
    var i, regex = [], map = {}; 
    for( i=0; i<findArray.length; i++ ){ 
        regex.push( findArray[i].replace(/([-[\]{}()*+?.\\^$|#,])/g,'\\$1') );
        map[findArray[i]] = replaceArray[i]; 
    }
    regex = regex.join('|');
    str = str.replace( new RegExp( regex, 'g' ), function(matched){
        return map[matched];
    });
    return str;
}
  
  