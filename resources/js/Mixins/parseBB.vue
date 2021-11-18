<script>
export default {
    data: () => ({
        bbDef:[
            {f: 'b', 'r':'strong'},
            {f: 'h2', 'r':'h2'},
        ]
    }),
    methods: {
        parseBB(sample){
            for (const iterator of this.bbDef) {
                sample = sample.replaceAll('['+iterator.f+']', '<'+iterator.r+'>')
                sample = sample.replaceAll('[/'+iterator.f+']', '</'+iterator.r+'>')
            }
            return sample
        },
    }
};
</script>

