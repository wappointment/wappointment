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
            refund: {
                method: 'post', 
                route: '/order/refund'
            },
            markpaid: {
                method: 'post', 
                route: '/order/paid'
            },
            cancel: {
                method: 'post', 
                route: '/order/cancel'
            },
        };
    }

}