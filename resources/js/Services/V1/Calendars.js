import ApiV1 from './ApiV1'
export default class CalendarsService extends ApiV1 {
        endpoints() {
            return {
                save: { 
                    method: 'post', 
                    route: '/calendars' 
                },
                index: {
                    method: 'get',
                    route: '/calendars'
                },
                delete: {
                    method: 'delete',
                    route: '/calendars'
                },
                reorder: {
                    method: 'post',
                    route: '/calendars/reorder'
                },
                getAvatar: {
                    method: 'post',
                    route: '/calendars/avatar'
                },
            };
        }
    }