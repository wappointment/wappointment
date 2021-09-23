<template>
    <WapModal :show="show" @hide="hideModal" v-if="show">
          <h4 slot="title" class="modal-title">Thanks for updating Wappointment
          </h4>
          <div class="update-section">
              <h3 class="d-flex align-items-center">
                <img :src="getUpdateImg" class="img-fluid mr-3" alt="What's new in Wappointment">
                <span>V2.3.0</span>   
                <span class="ml-2">Selling services with ease</span>
              </h3>
              <div class="d-flex flex-align-items-center">
                  <div><img :src="wappoVersionImage('230', 'payment_process.gif')" alt="payment process" class="img-fluid img-update" title="payment process"/></div>
                  <div class="ml-3">
                      <p>Many of you wanted a cleaner process to sell your services detached from WooCommerce.</p>
                        <p>So we did just that, you can now:</p>
                        <ol>
                            <li>Set a price for each of your services in your favourite currency</li>
                            <li>Receive payments on site when the customer comes on the day of his appointment</li>
                            <li>Get paid online with Paypal and Stripe with our <a :href="toAddonsPage('2newaddons')" target="_blank">2 new addons</a></li>
                            <li>Sell your services within packages with our <a :href="toAddonsPage('package_addon')" target="_blank">package addon</a> e.g.:
                            <ol class="bg-secondary mt-2 m-0 px-4 py-2 rounded">
                                <li>Sell 10 hours of consultation for $600 expiring after 30 days</li>
                                <li>Sell 30 hours of consultation for $1500 expiring after 90 days</li>
                                <li>Sell 50 hours of consultation for $3000 never expiring</li>
                            </ol> 
                            </li>
                        </ol>
                  </div>
              </div>
                
                
            
            <div class="p-4 ml-4"  v-if="false">
                <h5>Changelog 2.3.0</h5>
                <ol>
                    <li>Improved service interfaces, for a clearer view of what you are selling and how much it cost</li>
                    <li>added orders menu, for all of the sales, pending, cancelled, awaiting payment</li>
                    <li>added option to open slots for a new day from a specific time</li>
                </ol>
            </div>
            <div class="wprevious-version" v-if="false">
                <a href="javascript:;" @click="showPrevious">Click to reveal 2.2.0 Changelog</a>
                <div v-if="previous">
                    <div>
                        <span class="h5 text-muted">Below is how your staff selection page looks like now.</span>
                        <img :src="wappoVersionImage('220', 'staff_page.gif')" alt="staff page" class="img-fluid img-update" title="staff page"/>
                    </div>
                    <div class="text-muted">
                        <h5 class="text-muted">Changelog 2.2.0</h5>
                        <ol>
                            <li>Added staff selection page in booking form </li>
                            <li>First booking widget screen can now be a staff selection page using a shortcode attribute </li>
                            <li><strong>Optimization made</strong> on calendars availability check requests</li>
                            <li>Added Staff field, for extra information describing your staff(to be used in emails and SMS reminders)</li>
                            <li>Added <strong>staff permissions</strong> to allow staff to modify their own availability, etc...</li>
                            <li>added <strong>appointment history</strong> shortcode for logged in users [wap_history]</li>
                            <li>Admin notifications emails are now translatable with LocoTranslate or other translations system alike</li>
                            <li>Improved overall usability in the backend interfaces</li>
                        </ol>
                    </div>
                </div>
            </div>
          </div>
    </WapModal>
</template>
<script>
import abstractView from './Views/Abstract'

export default {
    extends: abstractView,
    data: () => ({
        show: true,
        images : [1,2],
        image_number: 1,
        previous: false
    }),
     
    computed:{
        getUpdateImg(){
            return window.apiWappointment.apiSite + '/plugin/' + window.apiWappointment.version + '/update.png'
        },
    },

    methods:{
        showPrevious(){
            this.previous = !this.previous
        },
        hideModal(){
            this.show = false
        },

        settingsSaved(){
            //nothing happens yet
        }
    },
   
    created(){
        this.show = [undefined,''].indexOf(window.wappointmentAdmin.canSeeUpdate) === -1
        if(this.show){
            this.settingStaffSave('viewed_updates', true, this.settingsSaved) 
        }
    },
}
</script>
<style >
.wrapping-update{
    width: 450px;
}
.update-section .intro{
    background-color: #6b6a8e;
    font-size: 1.25rem;
    padding: 1rem;
    border-radius: 1rem;
    color: #fff;
    line-height: 1.8;
    margin: 1rem 0;
}
.update-section pre{
    background: #353535;
    border-radius: 1rem;
    padding: 0 1rem;
    font-size: .8em;
}
.update-section a{
    color: var(--orange);
}
.img-update{
    border: 2px solid #f0f0f0;
    border-radius: 1em;
    margin-bottom: 1em;
}
.wprevious-version{
    background: #eee;
    border-radius: 1.4em;
    padding: 1em;
}
</style>
