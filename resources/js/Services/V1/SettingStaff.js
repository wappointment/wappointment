import ApiV1 from './ApiV1'
export default class SettingStaffService extends ApiV1 {

    endpoints() {
        return {
            save: { 
                method: 'post', 
                route: '/settingsstaff' 
            },
            delete: {
                method: 'delete',
                route: '/settingsstaff'
            },
            get: {
                method: 'get',
                route: '/settingsstaff'
            },
            saveCal: { 
                method: 'post', 
                route: '/settingsstaff/savecal'
            },
            disconnectCal: { 
                method: 'post', 
                route: '/settingsstaff/disconnect'
            },
            refreshCalendars: { 
                method: 'post', 
                route: '/settingsstaff/refreshcalendars' 
            },
        };
    }

}