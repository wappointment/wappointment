import ApiV1 from './ApiV1'
export default class StatusService extends ApiV1 {

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