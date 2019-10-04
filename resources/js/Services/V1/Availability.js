import ApiV1 from './ApiV1'
export default class AvailabilityService extends ApiV1{

    endpoints() {
        return {
            get: {
                method: 'get',
                route: 'availability'
            }
        };
    }

}