export default class ServiceService {

    endpoints() {
        return {
            save: { 
                method: 'post', 
                route: 'service'
            },
            get: {
                method: 'get',
                route: 'settings'
            }
        };
    }

}