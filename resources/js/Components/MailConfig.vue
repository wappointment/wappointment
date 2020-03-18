<template>
    <div class="reduced" v-if="viewData!== null">
        <ErrorList :errors="errorMessages" className="popupErrors"></ErrorList>
        <FormGenerator ref="mcformgenerator" :schema="schema" :data="sendconfig" 
        @submit="sendTestEmail" @back="$emit('back')" :buttons="false" @changedValue="changedValue" :key="formKey" 
        labelButton="Save" @ready="readytosubmit">
        </FormGenerator>
        <div v-if="isSendmail" class="form-group valid col-md-12 field-input">
            <a href="javascript:;" class="small" v-if="!clickedAdvanced" @click="clickedAdvanced=!clickedAdvanced">More configuration</a>
            <div v-else >
                <label for="sendmail-configuration" ><span>Sendmail command</span></label> 
                <div class="field-wrap">
                    <div class="wrapper">
                        <input id="sendmail-configuration" v-model="sendconfig.sendmailcmd"  
                        placeholder="e.g.: /usr/sbin/sendmail -bs" type="text" class="form-control">
                    </div>
                </div>
            </div>
        </div>
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
import momenttz from '../appMoment'
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
              fields: [
                {
                    type: "input",
                    label: "Username",
                    model: "username",
                    required: true,
                    cast: String,
                    validation: ['required'],
                    conditions: [
                        { model:'method', values: ['smtp'] }
                    ],
                },
                {
                    type: "password",
                    label: "Password",
                    model: "password",
                    required: true,
                    cast: String,
                    validation: ['required'],
                    conditions: [
                        { model:'method', values: ['smtp'] }
                    ],
                },

              ]
            },

            {
              type: 'row',
              class: 'd-flex flex-wrap flex-sm-nowrap align-items-center',
              classEach: 'mr-2',
              fields: [
                {
                    type: "input",
                    label: "Host",
                    model: "host",
                    required: true,
                    cast: String,
                    validation: ['required'],
                    conditions: [
                    { model:'method', values: ['smtp'] }
                    ],
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
                    conditions: [
                    { model:'method', values: ['smtp'] }
                    ],
                },
                {
                    type: "select",
                    labelDefault: 'Select encryption',
                    model: "encryption",
                    cast: String,
                    elements: [{id:'', name: 'none'},{id:'ssl', name: 'ssl'},{id:'tls', name: 'tls'} ],
                    labelKey: 'name',
                    conditions: [
                    { model:'method', values: ['smtp'] }
                    ],
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
            if(this.sendconfig.method == 'mailgun') {
                if(this.sendconfig.mgkey != '' && this.sendconfig.mgdomain != '') return true
            } else if(this.sendconfig.method == 'sendgrid') {
                if(this.sendconfig.sgkey != '' && this.sendconfig.sgkeyname != '') return true
            } else if(this.sendconfig.method == 'smtp') {
                if(this.sendconfig.username != '' && this.sendconfig.password != '' && this.sendconfig.host != '' && this.sendconfig.port >= 0) return true
            }else if(this.sendconfig.method == 'sendmail') {
                return true
            }else if(this.sendconfig.method == 'wpmail') {
                return true
            }
            return false
        },
        showSmtpPorts(){
            return this.sendconfig.method=='smtp' && this.ports !== false
        },
        schemas(){
            let originalCopy = this.originalSchema()

            originalCopy.fields = _.compact( _.map(originalCopy.fields, this.filterFields) ) ;

            return {fields: _.compact(originalCopy.fields)};
        },
        hasMethod() {
            return this.sendconfig.method != ''
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
      },
      readytosubmit(ready){
          this.formready = ready
      },

      setNewConfig(encryption, port) {
          this.sendconfig.encryption = encryption
          this.sendconfig.port = port
      },
      filterFields(a) {
        
            if(['method'].indexOf(a.model)!==-1 ) return a
            
            if(this.isWpMail && a.groupKey == 'wpmail' ) {
                 return a
            }

            if(this.hasApi && a.groupKey == 'mg' ) {
                 return a
            }
            if(this.isSendgrid && a.groupKey == 'sg' ) {
                 return a
            }

/*             if(this.isSendmail && a.groupKey == 'sendmail' ) {
                return a;
            } */
            
            if(this.isSmtp && a.groupKey == 'smtp' ) {
                return a
            }
      },

      save(){

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

      originalSchema(){
        return {
                fields: [
                
                {
                    type: "bschecklist",
                    label: "Sending method",
                    model: "method",
                    buttonMode: true,
                    radioMode: true,
                    required: true,
                    values: [
                    { value: 'wpmail', name:'WP mail', sub: 'Simple to setup, but can be unreliable', subclass:'tt-danger'}, 
                    { value: 'mailgun', name:'Mailgun API', sub: 'Recommended for setup and deliverability', subclass:'tt-success'},
                    { value: 'sendgrid', name:'SendGrid API', sub: 'Recommended for setup and deliverability', subclass:'tt-success'},
                    { value: 'smtp', name:'SMTP', sub: 'For experts only', subclass:'tt-info'},
                    ],
                    styleClasses: 'col-md-12'
                },
                {
                    type: "label",
                    label: "You can only send text versioned email with WP mail",
                    styleClasses: 'col-md-12',
                    groupKey: 'wpmail'
                },
                {
                    type: "label",
                    label: "Don't have a MailGun account? <a href='https://signup.mailgun.com/new/signup' target='_blank'>Signup for free</a>",
                    styleClasses: 'col-md-12',
                    groupKey: 'mg'
                },
                {
                    type: "radios",
                    model: "mgarea",
                    label: "Area",
                    values: [
                    { value: '', name:'US'},
                    { value: 'eu', name:'Europe'},
                    ],
                    required: true,
                    styleClasses: 'col-md-12',
                    groupKey: 'mg'
                },
                {
                    type: "input",
                    label: "API Domain",
                    model: "mgdomain",
                    placeholder: 'e.g.: mg.mydomain.com',
                    required: true,
                    validator: ['string'],
                    styleClasses: 'col-md-12',
                    groupKey: 'mg'
                },
                {
                    type: "input",
                    label: "API key",
                    placeholder: 'e.g.: key-da7175885eecdffb1df2bc092d13ec33',
                    model: "mgkey",
                    required: true,
                    validator: ['string'],
                    styleClasses: 'col-md-12',
                    groupKey: 'mg'
                },
                {
                    type: "label",
                    label: "Don't have a SendGrid account? <a href='https://signup.sendgrid.com/' target='_blank'>Signup for free</a>",
                    styleClasses: 'col-md-12',
                    groupKey: 'sg'
                },
                {
                    type: "input",
                    label: "SendGrid Key name",
                    model: "sgkeyname",
                    placeholder: 'e.g.: mykeyname',
                    required: true,
                    validator: ['string'],
                    styleClasses: 'col-md-12',
                    groupKey: 'sg'
                },
                {
                    type: "input",
                    label: "SendGrid API key",
                    placeholder: 'e.g.: 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',
                    model: "sgkey",
                    required: true,
                    validator: ['string'],
                    styleClasses: 'col-md-12',
                    groupKey: 'sg'
                },
                
                {
                    type: "radios",
                    model: "smtppreset",
                    values: [
                    { value: '', name:'Custom'},
                    { value: 'mailgun', name:'Mailgun'},
                    { value: 'sendgrid', name:'Sendgrid'},
                    ],
                    styleClasses: 'col-md-12',
                    groupKey: 'smtp'
                },
                {
                    type: "input",
                    label: "Username",
                    model: "username",
                    required: true,
                    validator: ['string'],
                    styleClasses: 'col-md-6',
                    groupKey: 'smtp'
                },
                {
                    type: "bspassword",
                    label: "Password",
                    model: "password",
                    required: true,
                    validator: ['string'],
                    styleClasses: 'col-md-6',
                    groupKey: 'smtp'
                },
                {
                    type: "input",
                    label: "Host",
                    model: "host",
                    required: true,
                    validator: ['string'],
                    styleClasses: 'col-md-7',
                    groupKey: 'smtp'
                },
                {
                    type: "input",
                    label: "Port",
                    inputType: "number",
                    model: "port",
                    required: true,
                    min: 0,
                    max: 65535,
                    validator: ['number'],
                    styleClasses: 'col-md-2',
                    groupKey: 'smtp'
                },
                {
                    type: "select",
                    label: "Encryption",
                    selectOptions: {
                        noneSelectedText: 'none'
                    },
                    model: "encryption",
                    values: ['ssl', 'tls'],
                    validator: ['string'],
                    styleClasses: 'col-md-3',
                    groupKey: 'smtp'
                },
                
                ]
                
            };
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
.vue-form-generator .field-label label span {
    display: block;
    width: 100%;
    margin-left: 0px;
    padding: .6rem;
    background-color: #f3f4f4;
    border-radius: .4rem;
    font-size: .9rem;
}
</style>
