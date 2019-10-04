import ApiV1 from './ApiV1'
export default class BookingService extends ApiV1{

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