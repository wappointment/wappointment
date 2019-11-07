import ApiV1 from './ApiV1'
export default class AppService extends ApiV1{

    endpoints() {
        return {
            freshinstall: {
                method: 'post',
                route: 'freshinstall',
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
            },
        };
    }

}