import ApiV1 from './ApiV1'
export default class EditorService extends ApiV1 {

    endpoints() {
        return {
            steps: {
                method: 'get',
                route: '/editor/steps'
            },
        };
    }

}