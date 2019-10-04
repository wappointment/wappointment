import ApiV1 from './ApiV1'
export default class AppointmentService extends ApiV1 {

    endpoints() {
        return {
            get: {
                method: 'get',
                route: 'appointment'
            },
            cancel: {
                method: 'patch', 
                route: 'appointment'
            },
        };
    }
}