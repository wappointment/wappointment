import ApiV1 from './ApiV1'
export default class ServiceService extends ApiV1 {

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