export default function(prefix = '') { 
    let id = '';
    let chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    
    for (var i = 0; i < 10; i++){
        id += chars.charAt(Math.floor(Math.random() * chars.length))
    }
    
    return prefix + id;
}