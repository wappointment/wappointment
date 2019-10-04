import ApiV1 from './ApiV1'
export default class ClientService extends ApiV1{

    endpoints() {
        return {
            save: { 
                method: 'post', 
                route: 'client'
            },
            get: { 
                method: 'get', 
                route: 'client'
            },
            search: { 
                method: 'post', 
                route: 'clientsearch'
            },
        };
    }

}