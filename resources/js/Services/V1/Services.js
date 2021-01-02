import ApiV1 from './ApiV1'
export default class ServicesService extends ApiV1 {
        endpoints() {
            return {
                save: { 
                    method: 'post', 
                    route: '/services/service' 
                },
                index: {
                    method: 'get',
                    route: '/services/service'
                },
                delete: {
                    method: 'delete',
                    route: '/services/service'
                },
                reorder: {
                    method: 'post',
                    route: '/services/service/reorder'
                },
            };
        }
    }