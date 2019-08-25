
export default class WappointmentService {

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
        };
    }

}