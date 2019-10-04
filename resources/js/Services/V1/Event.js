import ApiV1 from './ApiV1'
export default class ServiceEvent extends ApiV1{

    endpoints() {
        return {
            save: { 
                method: 'post', 
                route: 'events'
            },
            get: {
                method: 'get',
                route: 'events'
            },
            patch: {
                method: 'patch',
                route: 'events'
            },
            delete: { 
                method: 'delete', 
                route: 'events'
            },
            confirm: { 
                method: 'put', 
                route: 'events'
            }
        };
    }

}