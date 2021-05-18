export default class OrderService{
    suffix(){
        return 'wappointment/v1'
    }
    endpoints() {
        return {
            create: { 
                method: 'post', 
                route: '/order/create',
            },
            payd: {
                method: 'post', 
                route: '/order/pay'
            }, 
        };
    }

}