<template>
  <div v-if="canShow">
      <SlideIn :flex="false" :disappearOnCheck="false">
          <template slot="intro">
             <span class="dashicons-before dashicons-wappointment text-primary"></span> <Count>1</Count> message for you
          </template>
          <template slot="modal-title">{{ days }} days already!!</template>
          <template slot="modal-body">
             <div>
                 <div class="d-flex" v-if="!experience">
                    <div>
                        <h1>{{ days }} days already!!</h1>
                        <p class="h5">Thank you so much for your trust in Wappointment!</p>
                        <p class="h5">It took us a full year of work to get that plugin in your hands.</p>
                        <h3>How is your experience with Wappointment so far?</h3>
                        <HowYouFeel @changed="feelingChanged"/>
                        <div >
                            <button class="btn btn-link btn-sm" @click="dontShowAgain">Don't show again</button>
                        </div>
                    </div>
                    <div>
                        <figure class="figure">
                            <img :src="pathAssets('images/yeah.jpg')" class="img-fluid" height="200" alt="Our little team of 2">
                            <figcaption class="figure-caption text-right">Our little team of 2</figcaption>
                        </figure>
                    </div>
                </div>
                <div v-else>
                    <div class="d-flex h3"><HowYouFeel :feeling="experience" @changed="feelingChanged"/></span> </div>
                    <hr>
                    <div v-if="!changingNow">
                        <div v-if="happy">
                            <div class="d-flex">
                                <div>
                                    <div v-if="!messageSent">
                                        <p v-if="great" class="h1">Wow! Thank you so much!</p>
                                        <p v-else class="h1">Glad to hear it's fun so far</p>
                                        <p class="h5">We're trying to improve. Can you help?</p>
                                        <p class="h5">We just want to know you a bit better.</p>
                                        <p class="h5">Just saying "Hi" is plenty already.</p>
                                        <p class="h6">But if you want to write a bit more about your site and yourself, that would be great!</p>
                                    </div>
                                    <Contact :autofill="autofillHappy" @sent="sent"></Contact>
                                </div>
                                <div>
                                    <figure class="figure">
                                        <img :src="pathAssets('images/yeah.jpg')" class="img-fluid" height="200" alt="Our little team of 2">
                                        <figcaption class="figure-caption text-right">Our little team of 2</figcaption>
                                    </figure>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <div v-if="!messageSent">
                                <p class="h1">Ouch! What happened?</p>
                                <p class="h5">How could we make your experience better?</p>
                                <p class="h5">Is it buggy? Is it slow? Is it lacking features you need? Be honnest!</p>
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
import SlideIn from './SlideIn'
import Count from './Count'
import HowYouFeel from './HowYouFeel'
import Contact from '../Wappointment/Contact'

export default {
    extends: abstractView,
    components:{ SlideIn, Count, Contact,HowYouFeel},
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
        if(window.wappointmentAdmin.updatePages.length > 0 ){
            this.versions = window.wappointmentAdmin.updatePages
        }
        if(window.wappointmentAdmin.helloIgnore != '') this.helloIgnore = window.wappointmentAdmin.helloIgnore
    },
}
</script>
