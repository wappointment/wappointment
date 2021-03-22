export default class Location {
    suffix(){
        return 'wappointment/v1'
    }
    endpoints() {
        return {
            save: { 
                method: 'post', 
                route: '/services/location' 
            },
            index: {
                method: 'get',
                route: '/services/location'
            },
            delete: { 
                method: 'delete',  
                route: '/services/location' 
            },
        };
    }
}