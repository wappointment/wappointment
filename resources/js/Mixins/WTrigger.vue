<script>
export default {
    methods: {

        triggerWEvent(eventName, eventData){
            let event = null
            if(typeof(Event) === 'function') {
                event = new Event(eventName)
            }else{
                event = document.createEvent('Event')
                event.initEvent(eventName, true, true)
            }
            
            event.wdata = eventData
            document.dispatchEvent(event)
        },
        cleanString: function (string) {
            let doc = new DOMParser().parseFromString(string, 'text/html')
            return doc.body.textContent || ''
        },
    }
};
</script>