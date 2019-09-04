
export default class AddonsService {

    endpoints() {
        return {
            get: { 
                method: 'get',
                route: '/addons',
                timeout: 20000
            },
            register: { 
                method: 'post',
                route: '/addons',
                timeout: 20000
            },
            install: { 
                method: 'post',
                route: '/addons/install',
                timeout: 20000
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
                route: '/addons/check',
                timeout: 20000
            },
        };
    }

}