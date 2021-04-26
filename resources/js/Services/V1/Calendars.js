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
                saveService:{
                    method: 'post',
                    route: '/calendars/services'
                },
                saveCustomFields:{
                    method: 'post',
                    route: '/calendars/customfields'
                },
                savePermission:{
                    method: 'post',
                    route: '/calendars/permissions'
                },
                toggle: {
                    method: 'post',
                    route: '/calendars/toggle'
                },
                getAvatar: {
                    method: 'post',
                    route: '/calendars/avatar'
                },
                saveCal: { 
                    method: 'post', 
                    route: '/calendars/savecal'
                },
                refreshCalendars: { 
                    method: 'post', 
                    route: '/calendars/refreshcalendars'
                },
                disconnectCal: { 
                    method: 'post', 
                    route: '/calendars/disconnect'
                },
            };
        }
    }