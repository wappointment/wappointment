export default class ClientService {

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