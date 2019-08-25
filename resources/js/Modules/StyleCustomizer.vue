<script>
import Colors from './Colors'
export default {
   mixins: [Colors],
   data: () => ({
        btnHover: false,
        defaultBtnColor: '#576D85'
    }),
  computed:{
        styleButton(){
          if(this.options === undefined || this.options.button === undefined) return ''
          
          let bgcol = this.hexToRgbA(this.options.button.backgroundColor !== undefined ? this.options.button.backgroundColor : this.defaultBtnColor) 
          let text = this.hexToRgbA(this.options.button.color)
          
          return this.btnHover === true ? {
            backgroundColor: this.toStringRgba(bgcol,.9),
            borderColor: this.toStringRgba(bgcol,.9) ,
            boxShadow: '0 0 0 .2rem '+ this.toStringRgba(bgcol,.5)
          } : {
            backgroundColor: this.toStringRgba(bgcol) ,
            borderColor: this.toStringRgba(bgcol) ,
            boxShadow: 'none',
            color: this.toStringRgba(text) ,
          };
        },

    },

    methods: {
        changeStyle(hover){
          this.btnHover = hover
        },

    }
};
</script>