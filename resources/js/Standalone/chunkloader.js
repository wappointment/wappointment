import LoadingComponent from '../Components/Loaders/Bar'
import ErrorComponent from '../Components/Error'
export default function(imported) { 
    return {
        component: imported,
        loading: LoadingComponent,
        error: ErrorComponent,
        delay: 100,
        timeout: 30000
    }
}