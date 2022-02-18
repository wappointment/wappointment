import ApiV2 from './ApiV2'
export default class PostsService extends ApiV2{

    endpoints() {
        return {
            create: { 
                method: 'post',
                route: '/posts',
            },
        };
    }

}