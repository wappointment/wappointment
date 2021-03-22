import ApiV1 from './ApiV1'
export default class ServicesService extends ApiV1 {
        endpoints() {
            return {
                save: { 
                    method: 'post', 
                    route: '/services' 
                },
                index: {
                    method: 'get',
                    route: '/services'
                },
                delete: {
                    method: 'delete',
                    route: '/services'
                },
                reorder: {
                    method: 'post',
                    route: '/services/reorder'
                },
            };
        }
    }