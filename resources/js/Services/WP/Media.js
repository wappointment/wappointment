import ApiV2 from './ApiV2'
export default class MediaService extends ApiV2{

    endpoints() {
        return {
            get: { 
                method: 'get',
                route: '/media',
            },
            create: { 
                method: 'post',
                route: '/media',
            },
        };
    }

}