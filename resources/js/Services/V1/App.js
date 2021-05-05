import ApiV1 from './ApiV1'
export default class AppService extends ApiV1{

    endpoints() {
        return {
            updatepage: {
                method: 'post',
                route: 'updatepage',
            },
            freshinstall: {
                method: 'post',
                route: 'freshinstall',
            },
            refreshcache: {
                method: 'post',
                route: 'refreshcache',
            },
            wizardlater: {
                method: 'post',
                route: 'wizardlater',
            },
            wizard: {
                method: 'post',
                route: 'wizard',
            },
            migrate: {
                method: 'post',
                route: 'app/migrate',
                timeout: 30000
            },
            connectdotcom: {
                method: 'post',
                route: 'wappointment/connect',
            },

            disconnectdotcom: {
                method: 'post',
                route: 'wappointment/disconnect',
            },
            refreshdotcom: {
                method: 'post',
                route: 'wappointment/refresh',
            },
        };
    }

}