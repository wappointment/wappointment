import ApiV1 from './ApiV1'
export default class ReminderService extends ApiV1 {

    endpoints() {
        return {
            sendPreview: { 
                method: 'post', 
                route: 'reminderpreview'
            },
            save: { 
                method: 'post', 
                route: 'reminder'
            },
            patch: { 
                method: 'patch', 
                route: 'reminder'
            },
            toggle: {
                method: 'patch', 
                route: 'reminder'
            },
            get: { 
                method: 'get', 
                route: 'reminder'
            },
            delete: { 
                method: 'delete', 
                route: 'reminder'
            }
        };
    }

}