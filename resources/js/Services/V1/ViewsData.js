import ApiV1 from './ApiV1'
export default class ViewsDataService extends ApiV1 {

    endpoints() {
        return {
            get: {
                method: 'get',
                route: '/viewsdata'
            },
            calendar: {
                method: 'get',
                route: '/config/calendar'
            }
        };
    }

}