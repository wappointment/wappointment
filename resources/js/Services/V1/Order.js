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
            pay: {
                method: 'post', 
                route: '/order/pay'
            }, 
            confirm: {
                method: 'post', 
                route: '/order/confirm'
            }, 
            index: { 
                method: 'get', 
                route: 'orders'
            },
        };
    }

}