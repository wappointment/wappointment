export default class SettingStaffService {

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