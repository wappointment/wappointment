export default class CustomFields {
    suffix(){
        return 'wappointment/v1'
    }
    endpoints() {
        return {
            index: {
                method: 'get',
                route: '/services/custom_fields'
            },
        };
    }
}