import ApiV1 from './ApiV1'
export default class CurrencyService extends ApiV1{

    endpoints() {
        return {
            index: {
                method: 'get',
                route: 'currency',
            },
            save: {
                method: 'post',
                route: 'currency',
            },
        };
    }
}