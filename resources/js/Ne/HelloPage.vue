<template>
  <div v-if="canShow">
      <SlideIn :flex="false" :disappearOnCheck="false">
          <template slot="intro">
             <span class="dashicons-before dashicons-wappointment text-primary"></span> <Count>1</Count> message for you
          </template>
          <template slot="modal-title">{{ days }} days already!!</template>
          <template slot="modal-body">
             <div>
                 <div class="d-flex flex-wrap" v-if="!experience">
                     <div>
                        <figure class="m-2 mx-4 figure">
                            <img :src="'https://ps.w.org/wappointment/assets/equipowappo.jpg?rev=2151020'" 
                            class="img-fluid rounded" height="200" alt="Our little team of 2">
                            <figcaption class="figure-caption text-right">Our little team of 2</figcaption>
                        </figure>
                    </div>
                    <div class="contact-wrapper">
                        <h2>{{ days }} days already!!</h2>
                        <p class="h6">Thank you so much for your trust in Wappointment!</p>
                        <p class="h6">It took us a full year of work to get that plugin in your hands.</p>
                        <h6>How is your experience with Wappointment so far?</h6>
                        <HowYouFeel @changed="feelingChanged"/>
                        <div >
                            <button class="btn btn-link btn-sm" @click="dontShowAgain">Don't show again</button>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <div class="d-flex h3"><HowYouFeel :feeling="experience" @changed="feelingChanged"/></span> </div>
                    <hr>
                    <div v-if="!changingNow">
                        <div v-if="happy">
                            <div class="d-flex">
                                <div>
                                    <div class="contact-wrapper" v-if="!messageSent">
                                        <div>
                                            <div class="h3 text-center" data-tt="We need you!">Have some love? <strong>Help us grow!</strong></div>
                                            
                                            <div class="h2 bg-primary text-white text-center">
                                                <div class="d-flex paralelo" style="position: relative;">
                                                    <div class="in"></div>
                                                    <div class="paralellogram">"And spread the word!"</div>
                                                    <div class="out"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center my-4 justify-content-between">
                                            <div><strong>1 - Rate us on WordPress.org</strong></div>
                                            <div>
                                                <a data-tt="Give us exposure" class="btn btn-outline-secondary text-dark ml-2" 
                                                href="https://wordpress.org/support/plugin/wappointment/reviews/#new-post" target="_blank">
                                                    <span class="dashicons dashicons-star-filled"></span>
                                                    <span class="dashicons dashicons-star-filled"></span>
                                                    <span class="dashicons dashicons-star-filled"></span>
                                                    <span class="dashicons dashicons-star-filled"></span>
                                                    <span class="dashicons dashicons-star-filled"></span>
                                                    Rate us
                                                </a>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center my-4 justify-content-between">
                                            <div><strong>2 - Follow us, Like us</strong></div>
                                            <div>
                                                <div id="fb-root"></div>
                                                <script type="application/javascript" async defer crossorigin="anonymous" src="https://connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v7.0&appId=243075756791687&autoLogAppEvents=1"></script>
                                                <div class="fb-like mx-2" data-href="https://www.facebook.com/wappointment/" 
                                                data-width="" data-layout="standard" data-action="like" data-size="large" data-share="false">
                                                    <a href="https://www.facebook.com/wappointment/" target="_blank" class="d-flex align-items-center btn btn-outline-secondary text-dark btn-xs">
                                                        <span class="dashicons dashicons-thumbs-up"></span> Like us
                                                    </a>
                                                </div>
                                            </div>
                                            <a href="https://twitter.com/wappointments?ref_src=twsrc%5Etfw" target="_blank" 
                                            class="twitter-follow-button btn btn-outline-secondary m-2" data-show-count="false">
                                            <span class="dashicons dashicons-twitter"></span> Follow us</a>
                                            <script type="application/javascript" async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
                                        </div>
                                        <div class="my-2">
                                            <div><strong>3 - Subscribe to our newsletter</strong></div>
                                            <SubscribeNewsletter list="main" :defaultEmail="getEmail" />
                                        </div>
                                        <div class="my-2">
                                            <div><strong>4 - Talk about us</strong></div>
                                            <div>
                                                <div>Nobody talks better about us than our own users.</div> 
                                                <div>Have a blog, or a vlog? Want to write a full review about Wappointment? </div>
                                                <div><a href="https://wappointment.com/support" target="_blank">Contact us</a> we will give you a free licence on all of our addons so you can test them all.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>   
                                    <figure class="m-2 mx-4 figure">
                                        <img :src="'https://ps.w.org/wappointment/assets/equipowappo.jpg?rev=2151020'" class="img-fluid rounded" height="200" alt="Our little team of 2">
                                        <figcaption class="figure-caption text-right">Elena and Ben Wireframing Wappointment</figcaption>
                                    </figure>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <div class="contact-wrapper" v-if="!messageSent">
                                <p class="h2">Ouch! What happened?</p>
                                <p class="h6">How could we make your experience better?</p>
                                <p class="h6">Is it buggy? Is it slow? Is it lacking features you need? Be honnest!</p>
                            </div>
                            <Contact :autofill="autofillUnHappy" @sent="sent"></Contact>
                        </div>
                    </div>
                </div>
             </div>
          </template>
      </SlideIn>
  </div>
</template>
<script>
import abstractView from '../Views/Abstract'
import SlideIn from '../PopPanels/SlideIn'
import Count from '../Components/Count'
import HowYouFeel from './HowYouFeel'
import Contact from '../Wappointment/Contact'
import SubscribeNewsletter from '../Wappointment/SubscribeNewsletter'


export default {
    extends: abstractView,
    components:{ SlideIn, Count, Contact, SubscribeNewsletter, HowYouFeel},
    data: () => ({
        days: window.wappointmentAdmin.days,
        versions:false,
        experience:false,
        changingNow: false,
        messageSent:false,
        helloIgnore: false,
        viewName: 'serverinfo',
        parentLoad: false,
        serverObj: false
    }),

    computed:{
        getEmail(){
            return window.wappointmentAdmin.defaultEmail
        },
        autofillHappy(){
            return {
                subject: 'Happily using Wappointment for '+this.days+' days so far',
                message: `<p>Hi,</p>
                <p>I just wanted to say Hi!</p>`
                ,
                extra: {
                    experience: this.experience
                }
            }
        },
        autofillUnHappy(){
            return {
                subject: 'Not happy with Wappointment',
                message: `<p>Hi,</p>
                <p>I'm having the following problems with Wappointment:</p>
                <ol><li>First of all</li></ol>
                `,
                extra: {
                    experience: this.experience,
                },
                server: this.serverObj
            }
            
        },
        canShow(){
            if(!(this.days > 30 && this.versions === false && !this.helloIgnore)) return false
            this.initValueRequest().then(this.loaded)
            return true
        },
        great(){
            return this.experience > 4
        },
        happy(){
            return this.experience > 3
        },
    },
    methods: {
        feelingChanged(experience){
            this.experience = experience
            this.changingNow = true
            setTimeout(this.reloading, 100);
        },
        loaded(response){
            this.serverObj  = response.data.server
        },
        reloading(){
            this.changingNow = false
        },
        sent(){
            this.messageSent = true
            this.settingStaffSave('hello_page', true) 
        },
        dontShowAgain(){
            this.settingStaffSave('hello_page', 666) 
            this.helloIgnore = true
        }
    },
   
    created(){
        if(window.wappointmentAdmin.updatePages !== undefined && window.wappointmentAdmin.updatePages.length > 0 ){
            this.versions = window.wappointmentAdmin.updatePages
        }
        if(window.wappointmentAdmin.helloIgnore != '') this.helloIgnore = window.wappointmentAdmin.helloIgnore
    },
}
</script>
<style>
.dashicons-twitter {
    color: #1da1f2;
}
.dashicons-thumbs-up {
    color: #3b5999;
}
.dashicons-star-filled{
    color:#ffb900;
}

.paralelo .in{
    width: 35px;
    background: #fff;
    transform: rotate(14deg);
    position: absolute;
    height: 50px;
    left: -30px;
    top: -8px;
}
.paralelo .out{
    width: 35px;
    background: #fff;
    transform: rotate(14deg);
    position: absolute;
    height: 50px;
    right: -8px;
    top: -4px;
}
.paralelo .paralellogram{
    margin: 0 25px;
}
</style>