export default class AppService{

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
        };
    }

}