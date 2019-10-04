import ApiV1 from './ApiV1'
export default class SettingService extends ApiV1{

    endpoints() {
        return {
            save: { 
                method: 'post', 
                route: '/settings' 
            },
            delete: {
                method: 'delete',
                route: '/settings'
            },
            get: {
                method: 'get',
                route: '/settings'
            },
            sendtestemail: {
                method: 'post',
                route: '/settings/sendtestemail'
            }
        };
    }

}