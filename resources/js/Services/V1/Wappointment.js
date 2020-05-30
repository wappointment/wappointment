import ApiV1 from './ApiV1'
export default class WappointmentService extends ApiV1 {

    endpoints() {
        return {
            subscribe: { 
                method: 'post',
                route: '/wappointment/subscribe',
            },
            contact: { 
                method: 'post',
                route: '/wappointment/contact',
            },
            sendtestbooking: { 
                method: 'post',
                route: '/wappointment/sendtestbooking',
            },
            sendignore: { 
                method: 'post',
                route: '/wappointment/sendignore',
            },
            
        };
    }

}