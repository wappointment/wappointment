
export default class AddonsService {

    endpoints() {
        return {
            get: { 
                method: 'get',
                route: '/addons'
            },
            register: { 
                method: 'post',
                route: '/addons',
            },
            install: { 
                method: 'post',
                route: '/addons/install',
            },
            activate: { 
                method: 'post',
                route: '/addons/activate',
            },
            deactivate: { 
                method: 'post',
                route: '/addons/deactivate',
            },
            check: { 
                method: 'get',
                route: '/addons/check'
            },
        };
    }

}