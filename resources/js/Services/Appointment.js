export default class AppointmentService {

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