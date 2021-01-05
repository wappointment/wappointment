export default class BookingService{
    suffix(){
        return 'wappointment/v1'
    }
    endpoints() {
        return {
            save: { 
                method: 'post', 
                route: '/services/booking',
                timeout: 20000
            },
            bookadmin: {
                method: 'post', 
                route: '/services/booking/admin'
            }, 
            fetch: { 
                method: 'post', 
                route: '/services/booking/fetch' 
            },
            get: { 
                method: 'get', 
                route: '/services/booking' 
            },
            delete: { 
                method: 'delete', 
                route: '/services/booking' 
            }
        };
    }

}