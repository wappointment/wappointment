import ApiV1 from './ApiV1'
export default class ClientService extends ApiV1{

    endpoints() {
        return {
            search: { 
                method: 'post', 
                route: 'client/search'
            },
            book: { 
                method: 'post', 
                route: 'client/book'
            },
            index: { 
                method: 'get', 
                route: 'client'
            },
            save: { 
                method: 'post', 
                route: 'client'
            },
            
        };
    }

}