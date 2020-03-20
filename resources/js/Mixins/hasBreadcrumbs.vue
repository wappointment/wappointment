<script>
import BreadCrumbs from '../Components/BreadCrumbs'

export default {

  data() {
    return {
        currentView: false,
        subview: '',
        dynamicProps: {},
        crumbs: [],
        mainCrumbLabel: 'Home'
    };
  },
  components: {BreadCrumbs},
  computed:{
    subCompKey(){
      return this.currentView + this.subview
    }
  },
  methods: {
    goTo(crumb){
      this[crumb.target]()
      this.subview = (crumb.subview !== undefined) ? crumb.subview:''
    },

    goToMain() {
      this.currentView = false
      this.crumbs = []
    },
    setCrumb(newView, labelCrumb, methodCrumb, props={}){
        this.currentView = newView
        this.dynamicProps = props
        this.crumbs = [
            { target: 'goToMain', label: this.mainCrumbLabel},
            { target: methodCrumb, label: labelCrumb, disabled:true},
        ]
    },
    updateCrumb(crumbs, subview = '', props = {}) {
      this.crumbs = crumbs  
      this.subview = subview
      this.dynamicProps = props
    },
   
  }
};
</script>
