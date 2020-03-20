<template>
    <div class="reduced" v-if="viewData!== null">
        <ErrorList :errors="errorMessages" className="popupErrors"></ErrorList>
        <FormGenerator ref="mcformgenerator" :schema="schema" :data="sendconfig" 
            @submit="sendTestEmail" @back="$emit('back')" :buttons="false" @changedValue="changedValue" :key="formKey" 
            labelButton="Save" @ready="readytosubmit">
        </FormGenerator>
        <!-- Ports links -->
        <div v-if="showSmtpPorts" class="form-group valid col-md-12 field-radios">
            <small class="field-wrap ">
                Possible SMTP configurations:
                <span v-for="(eports,encryption) in ports">
                    <small>{{ encryption }}
                        <span v-for="(port, index) in eports">
                            <a data-tt="Apply configuration" href="javascript:;" @click="setNewConfig(encryption, port)" >{{ port }}</a><span v-if="index != eports.length - 1">, </span>
                        </span> 
                    </small>
                </span>
            </small> 
        </div>
 
        <div v-if="hasMethod">
            <hr>
            <!-- From address setup -->
            <div class="form-group valid required col-md-12 field-input">
                
                <div>
                    <div v-if="showFrom" class="d-flex">
                        <input class="form-control mr-2" type="text" v-model="sendconfig.from_name" >
                        <input class="form-control mr-2" type="text" v-model="sendconfig.from_address" >
                    </div>
                    <div v-else class="d-flex align-items-center">
                        <span class="m-0 mr-2">From Address: </span>
                        <a href="javascript:;"  @mouseover="showEditFrom=true" 
                        @mouseout="showEditFrom=false" title="Edit" class="text-dark" 
                        @click="showFrom=!showFrom">{{ from_name }} <small class="text-muted"><{{from_address}}></small> </a>
                        <span v-if="showEditFrom" class="text-primary small ml-2">Click to Edit</span>
                    </div>
                </div>
            </div>
            <!-- Submit button -->
            <div class="d-flex align-items-start">
                <div class="ml-2 d-flex align-items-center">
                    
                    <button type="button" class="btn  btn-primary mr-2" 
                    :class="{disabled: !canSend}" 
                    :disabled="!canSend" @click="$refs.mcformgenerator.submitTrigger()"><span class="dashicons dashicons-email"></span> Save and Send test email</button>
                    <div>
                        <div v-if="showRecipient">
                            <input id="preveiwemail" class="form-control mr-2" type="text" v-model="recipient" >
                        </div>
                        <div v-else>
                            <a href="javascript:;"  @mouseover="showEdit=true" @mouseout="showEdit=false" title="Edit" class="text-muted" @click="showRecipient=!showRecipient">{{ recipient }}</a>
                            <span v-if="showEdit" class="text-primary small">Click to Edit</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         
    </div>
    
</template>

<script>
import FormGenerator from '../Form/FormGenerator'
import abstractView from '../Views/Abstract'
import Validation from '../Modules/Validation'
export default {
  extends: abstractView,
  mixins: [Validation],
  components:{FormGenerator},
    mounted(){
        this.refreshInitValue()
    },
    
  data() {
    return {
        
        other: null,
        viewName: 'settingsmailer',
        parentLoad: false,
        sendconfig: {
            method: '',
            mgkey: '',
            mgdomain: '',
            sgkey:'',
            sgkeyname:'',
            sendmailcmd: '',
            username: '',
            password: '', 
            host: '',
            port: '',
            encryption: '',
            smtppreset: '',
            from_address: '',
            from_name: '',
        },
        recipient: '',
        showEdit: false,
        showRecipient: false,
        showEditFrom: false,
        showFrom: false,
        clickedAdvanced: false,
        ports: false,
        preset: '',
        formready: false,
        method: '',

        formKey: 'formmailconfig',
          schema: [

            // WP Mail
            {
                type: 'checkimages',
                label: 'How is it provided?',
                model: 'method',
                radioMode: true,
                cast: Array,
                images: [
                { value: 'wpmail', name:'WP mail', icon: 'map-marked-alt', sub: 'Simple to setup, but can be unreliable', subclass:'tt-danger'}, 
                { value: 'mailgun', name:'Mailgun API', icon: 'map-marked-alt', sub: 'Recommended for setup and deliverability', subclass:'tt-success'},
                { value: 'sendgrid', name:'SendGrid API', icon: 'map-marked-alt', sub: 'Recommended for setup and deliverability', subclass:'tt-success'},
                { value: 'smtp', name:'SMTP', icon: 'map-marked-alt', sub: 'For experts only', subclass:'tt-info'},
                ],
                validation: ['required']
            },
            {
                type: 'label',
                model: 'txt1',
                label: 'You can only send text versioned email with WP mail',
                conditions: [
                  { model:'method', values: ['wpmail'] }
                ],
            },

            // MailGun API
            {
                type: 'label',
                model: 'txt2',
                label: "Don't have a MailGun account? <a href='https://signup.mailgun.com/new/signup' target='_blank'>Signup for free</a>",
                conditions: [
                  { model:'method', values: ['mailgun'] }
                ],
            },
            {
                type: 'checkimages',
                label: 'Mailgun Area',
                model: 'mgarea',
                radioMode: true,
                images: [
                    { value: 'us', name:'United States'}, 
                    { value: 'eu', name:'Europe'},
                ],
                cast: String,
                validation: ['required'],
                conditions: [
                  { model:'method', values: ['mailgun'] }
                ],
            },
            {
                type: 'input',
                label: 'API Domain',
                model: 'mgdomain',
                cast: String,
                validation: ['required'],
                conditions: [
                  { model:'method', values: ['mailgun'] }
                ],
            },
            {
                type: 'input',
                label: 'API Key',
                model: 'mgkey',
                cast: String,
                validation: ['required'],
                conditions: [
                  { model:'method', values: ['mailgun'] }
                ],
            },


            // SendGrid API
            {
                type: 'label',
                label: "Don't have a SendGrid account? <a href='https://signup.sendgrid.com/' target='_blank'>Signup for free</a>",
                model: 'txt3',
                conditions: [
                  { model:'method', values: ['sendgrid'] }
                ],
            },

            {
                type: 'input',
                label: 'SendGrid Key name',
                model: 'sgkeyname',
                cast: String,
                validation: ['required'],
                conditions: [
                  { model:'method', values: ['sendgrid'] }
                ],
            },
            {
                type: 'input',
                label: 'SendGrid API Key',
                model: 'sgkey',
                cast: String,
                validation: ['required'],
                conditions: [
                  { model:'method', values: ['sendgrid'] }
                ],
            },


            // SMTP
            {
                type: 'checkimages',
                model: "smtppreset",
                radioMode: true,
                values: [
                { value: '', name:'Custom'},
                { value: 'mailgun', name:'Mailgun'},
                { value: 'sendgrid', name:'Sendgrid'},
                ],
                cast: String,
                conditions: [
                  { model:'method', values: ['smtp'] }
                ],
            },
            {
              type: 'row',
              class: 'd-flex flex-wrap flex-sm-nowrap align-items-center',
              classEach: 'mr-2',
              conditions: [
                { model:'method', values: ['smtp'] }
              ],
              fields: [
                {
                    type: "input",
                    label: "Username",
                    model: "username",
                    required: true,
                    cast: String,
                    validation: ['required'],
                },
                {
                    type: "password",
                    label: "Password",
                    model: "password",
                    required: true,
                    cast: String,
                    validation: ['required'],
                },

              ]
            },

            {
              type: 'row',
              class: 'd-flex flex-wrap flex-sm-nowrap align-items-center',
              classEach: 'mr-2',
              conditions: [
                { model:'method', values: ['smtp'] }
              ],
              fields: [
                {
                    type: "input",
                    label: "Host",
                    model: "host",
                    required: true,
                    cast: String,
                    validation: ['required'],
                },
                {
                    type: "number",
                    label: "Port",
                    model: "port",
                    required: true,
                    min: 0,
                    max: 65535,
                    cast: String,
                    validation: ['required','number'],
                },
                {
                    type: "select",
                    labelDefault: 'Select encryption',
                    model: "encryption",
                    cast: String,
                    elements: [{id:'', name: 'none'},{id:'ssl', name: 'ssl'},{id:'tls', name: 'tls'} ],
                    labelKey: 'name',
                },

              ]
            },
            
        ]
        
    }
  },
    watch: {
        preset(newval, old){
            if(old != newval) {
                this.ports = false
                switch (newval) {
                    case 'mailgun':
                        this.sendconfig.host = 'smtp.mailgun.org'
                        this.sendconfig.encryption = 'tls'
                        this.sendconfig.port = '587'
                        this.ports = {
                            tls: ['25','2525', '587'],
                            ssl: ['465']
                        }
                        break;
                    case 'sendgrid':
                        this.sendconfig.host = 'smtp.sendgrid.net'
                        this.sendconfig.encryption = 'tls'
                        this.sendconfig.port = '587'
                        this.ports = {
                            tls: ['25','2525', '587'],
                            ssl: ['465']
                        }
                        break;
                    case 'wpmail':
                        
                        break;
                    case '':
                        this.sendconfig.host = ''
                        this.sendconfig.encryption = ''
                        this.sendconfig.port = '25'
                        break;
                
                    default:
                        break;
                }
            }
            
        },
        sendconfig: {
            handler: function (val, oldval) { 
               val.host = val.host.trim()
               val.username = val.username.trim()
               val.password = val.password.trim()
               if(val.smtppreset !== undefined){
                   this.preset = val.smtppreset
               }
             },
            deep: true
        },  
    }, 
    computed: {
        from_address(){
            return this.sendconfig.from_address
        },
        from_name(){
            return this.sendconfig.from_name
        },
        canSend(){
            return this.formready
        },
        showSmtpPorts(){
            return this.sendconfig.method=='smtp' && this.ports !== false
        },

        hasMethod() {
            return [''].indexOf(this.method) === -1
        },
        isWpMail() {
            return ['wpmail'].indexOf(this.sendconfig.method) !== -1
        },
        hasApi() {
            return ['mailgun'].indexOf(this.sendconfig.method) !== -1
        },
        isSendgrid() {
            return ['sendgrid'].indexOf(this.sendconfig.method) !== -1
        },
        isSendmail(){
            return ['sendmail'].indexOf(this.sendconfig.method) !== -1
        },
        isSmtp(){
            return ['smtp'].indexOf(this.sendconfig.method) !== -1
        },
    },
  methods: {
      changedValue(newSendConfig){
          this.sendconfig = newSendConfig
          this.method = newSendConfig.method
      },
      readytosubmit(ready){
          this.formready = ready
      },

      setNewConfig(encryption, port) {
          this.sendconfig.encryption = encryption
          this.sendconfig.port = port
      },
   
      async sendTestEmailRequest(){
          return await this.serviceSetting.call('sendtestemail', {data: this.sendconfig, recipient: this.recipient}) 
      },

      sendTestEmail(){
          if(!this.canSend) return
          this.request(this.sendTestEmailRequest, undefined,undefined,false,  this.resultTestEmail)
      },
      resultTestEmail(e){
          this.$emit('mailConfigured')
          this.successRequest(e)
      },
      
      loaded(viewData){
          this.viewData = viewData.data
          this.sendconfig = viewData.data.mail_config
          this.recipient = this.viewData.recipient
      },
  },



}
</script>
<style>
.vue-form-generator .form-control[type=number] {
    padding: 0 .2rem;
}
.btn-lg .dashicons, .btn-lg .dashicons:before {
    width: auto;
    height: auto;
    font-size: 30px;
}
.vue-form-generator .text-primary {
    color: #3773b4 !important;
}
.btn-secondary.disabled, .btn-secondary:disabled {
    cursor: not-allowed;
    background-color: #6c757d;
    border-color: #6c757d;
    color: #fff;
}
.mail_configured{
    font-size: 40px;
    height: 40px;
    width: 40px;
}
.form-wrapppo .label-formgen {
    display: block;
    width: 100%;
    margin-left: 0px;
    padding: .6rem;
    background-color: #f3f4f4;
    border-radius: .4rem;
    font-size: .9rem;
}
</style>
