export default class SettingService{

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