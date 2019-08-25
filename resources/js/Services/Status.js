export default class StatusService {

    endpoints() {
        return {
            save: { 
                method: 'post', 
                route: 'status'
            },
            delete: { 
                method: 'delete', 
                route: 'status'
            }
        };
    }

}