export default class BookingService {

    endpoints() {
        return {
            save: { 
                method: 'post', 
                route: 'booking'
            },
            reschedule: {
                method: 'patch', 
                route: 'booking'
            },
            get: { 
                method: 'get', 
                route: 'booking'
            },
            delete: { 
                method: 'delete', 
                route: 'booking'
            }
        };
    }

}