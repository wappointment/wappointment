import ApiV1 from './ApiV1'
export default class SettingStaffService extends ApiV1 {

    endpoints() {
        return {
            save: { // method name
                method: 'post', // method to htpp request
                route: '/settingsstaff' // route to call with method
            },
            delete: {
                method: 'delete',
                route: '/settingsstaff'
            },
            get: {
                method: 'get',
                route: '/settingsstaff'
            }
        };
    }

}