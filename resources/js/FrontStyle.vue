<style>

.large-version .wap-wid{
    max-width: 420px;
}
.large-version .wap-wid.step-BookingCalendar{
    max-width: 100%;
    min-width: 100%;
}
.wap-wid{
    max-width: 360px;
}

.br-fixed .wap-wid .wbtn-booking{
  float:right;
}
.wap-wid .loader {
    min-height: 68px;
}

.wap-front a.initial{
  color:initial !important;
}

.wap-front .wbtn.wbtn-secondary.wbtn-cell{
  border-radius: .5em;
  margin: .4em 0;
}

.wap-front .wbtn {
  display: inline-block;
  font-weight: 400;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  border: 1px solid transparent;
  padding: 0.375em;
  font-size: .8em;
  line-height: 1.5;
  border-radius: 0.25em;
  transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  margin: 0;
  background-color: transparent;
  background: transparent;
  height: auto;
  text-transform: none;
}

.wap-front .wbtn-block{
  width: 100%;
}


  .wap-front .wbtn:hover, 
  .wap-front .wbtn:focus {
    text-decoration: none;
  }

  .wap-front .wbtn:focus, 
  .wap-front .wbtn.focus {
    outline: 0;
  }

  .wap-front .wbtn.wbtn-disabled, 
  .wap-front .wbtn:disabled {
    opacity: 0.65;
  }

  .wap-front .wbtn:not(:disabled):not(.wbtn-disabled) {
    cursor: pointer;
  }

  .wap-front .wbtn:not(:disabled):not(.wbtn-disabled):active, 
  .wap-front .wbtn:not(:disabled):not(.wbtn-disabled).active {
    background-image: none;
  }

  .wap-front a.wbtn.wbtn-disabled,
  .wap-front fieldset:disabled a.wbtn {
    pointer-events: none;
  }

.wap-front .d-flex {
  display: -ms-flexbox !important;
  display: flex !important;
}
.wap-front .d-flex.d-flex-inline{
    display: inline-flex !important;
}
.wap-front .flex-wrap {
    -ms-flex-wrap: wrap !important;
    flex-wrap: wrap !important;
}

.wap-front .flex-fill{
    -ms-flex: 1 1 auto !important;
    flex: 1 1 auto !important;
}
.wap-front .justify-content-between {
  -ms-flex-pack: justify !important;
  justify-content: space-between !important;
}

.wap-front .justify-content-around {
  -ms-flex-pack: distribute !important;
  justify-content: space-around !important;
}
.wap-front .justify-content-center {
    -webkit-box-pack: center !important;
    -ms-flex-pack: center !important;
    justify-content: center !important;
}
.wap-front .align-items-center {
  -ms-flex-align: center !important;
  align-items: center !important;
}
.wap-front .align-items-start {
    -webkit-box-align: start !important;
    -ms-flex-align: start !important;
    align-items: flex-start !important;
}
.wap-front .align-content-around {
  -ms-flex-line-pack: distribute !important;
  align-content: space-around !important;
}
.wap-front .align-self-center {
  -ms-flex-item-align: center !important;
  align-self: center !important;
}

.wap-front .align-self-stretch {
  -ms-flex-item-align: stretch !important;
  align-self: stretch !important;
}


.wap-wid.wclosable > .wclose {
    position: absolute;
    z-index: 2;
    right: .3em;
    top: .3em;
}

.wap-front .wml-2{
    margin-left:.4em;
}
html[dir=rtl] .wap-front .wml-2{
  margin-right: .4em !important;
  margin-left: 0 !important;
}
.wprice{
  font-weight: bold;
}

.wap-front .text-center{
    text-align: center;
}

.wap-front .text-sm{
  font-size: .7em;
}

.wappo-popped{
  overflow: hidden;
  position: relative;
}

.wap-front.wdesk.br-fixed {
  min-width: 360px;
}

.wap-front.br-fixed{
    position: fixed;
    right: 0;
    bottom: 0;
    margin: 1rem;
    max-height: 95%;
    min-width: 320px;
}

.wap-booking-fields {
    text-align: left;
    margin: .5em 0;
}

html[dir=rtl] .wap-front.br-fixed{
    right: auto;
    left: 0;
}

html[dir=rtl] .br-fixed .wap-wid .wbtn-booking {
  float: left;
}
html[dir=rtl] .wap-booking-fields{
    text-align: right;
}


.wap-booking-fields input,
.wap-booking-fields textarea{
  color: var(--wappo-body-tx) !important;
}

.wap-booking-fields .isInvalid input[type="text"], 
.wap-booking-fields .isInvalid input[type="email"], 
.wap-booking-fields .isInvalid input[type="url"], 
.wap-booking-fields .isInvalid input[type="tel"],
.wap-booking-fields .isInvalid textarea{
    border-right: 4px solid var(--wappo-error-tx) !important;
}
.wap-booking-fields .isValid input[type="text"], 
.wap-booking-fields .isValid input[type="email"], 
.wap-booking-fields .isValid input[type="url"], 
.wap-booking-fields .isValid input[type="tel"],
.wap-booking-fields .isValid textarea{
    border-right: 4px solid var(--wappo-valid-tx) !important;
}

html[dir=rtl] .wap-booking-fields .isInvalid input[type="text"], 
html[dir=rtl] .wap-booking-fields .isInvalid input[type="email"], 
html[dir=rtl] .wap-booking-fields .isInvalid input[type="url"], 
html[dir=rtl] .wap-booking-fields .isInvalid input[type="tel"],
html[dir=rtl] .wap-booking-fields .isInvalid textarea{
    border-left: 4px solid var(--wappo-error-tx) !important;
    border-right:1px solid var(--wappo-input-bor) !important;
}
html[dir=rtl] .wap-booking-fields .isValid input[type="text"], 
html[dir=rtl] .wap-booking-fields .isValid input[type="email"], 
html[dir=rtl] .wap-booking-fields .isValid input[type="url"], 
html[dir=rtl] .wap-booking-fields .isValid input[type="tel"],
html[dir=rtl] .wap-booking-fields .isValid textarea{
    border-left: 4px solid var(--wappo-valid-tx) !important;
    border-right:1px solid var(--wappo-input-bor) !important;
}


.wap-booking-fields .field-required label::after {
    content:" *";
    color:var(--wappo-error-tx);
}

.wap-front .wappointment-errors{
    background-color:var(--wappo-error-tx);    
    border-radius:.25em;
    padding: .3em;
    margin: .5em 0;
}

.wap-front div.wappointment-errors div{
    color: var(--wappo-pri-tx);
}

.wap-front .wrap-calendar div.wappointment-errors{
    color: var(--wappo-pri-tx);
    font-size: .9em;
}
.phone-field-wrap .dpselect .selection::after,
.phone-field-wrap strong{
    color: #645a5a;
}

.wap-front.poppedup{
  width: 100%;
  height: 100%;
  margin: 0;
  position: fixed;
  left: 0;
  bottom: 0;
  overflow-y: scroll;
}

.wap-front.poppedup .wappo_module{
  display: flex;
  align-items: center;
  width: 100%;
  height: 100%;
  position: relative;
  z-index: 99;
}
.wap-front.poppedup .wappo_module .wap-wid{
  width: 500px;
  margin: 0 auto;
  position: relative;
  max-width:none;
}
.wap-front.poppedup .wap-bg{
  position: fixed;
  height: 100%;
  width: 100%;
  background-color: rgba(0, 0, 0, .7);
  top: 0;
  z-index: 0;
  overflow-y: scroll;
  margin: 0 auto;
}

.wap-front .wap-form-body{
    background-color: var(--wappo-body-bg);
}

.wap-front .wap-form-body,
.wap-front .wlabel,
.wap-front .wrap-calendar div, 
.wap-front .wrap-calendar div a
{
    color: var(--wappo-body-tx);
}

.wap-bf{
    transition: box-shadow ease-in-out .3s;
}
.wap-bf.show{
    box-shadow: 0px 8px 10px 0 rgba(0,0,0,.08);
    overflow: hidden;
    position: relative;
}

.wap-front .mr-2,
.wap-front .mx-2{
    margin-right: .4em !important;
}
html[dir=rtl] .wap-front .mr-2, 
html[dir=rtl] .wap-front .mx-2 {
  margin-right: 0 !important;
  margin-left: .4em !important;
}

.wap-front .ml-2,
.wap-front .mx-2{
    margin-left: .4em !important;
}

.wap-front .mb-2,
.wap-front .my-2{
    margin-bottom: .4em !important;
}

.wap-front .mt-2,
.wap-front .my-2{
    margin-top: .4em !important;
}
.wap-front .p-2 {
    padding: .5em !important;
}
.wap-front .pl-2 {
    padding-left: .5em !important;
}
.wap-front [data-tt]:not([data-tt=""]) {
  cursor: pointer;
}
.wap-front [data-tt]:hover {
  position: relative;
  z-index: 2;
}

.wap-front [data-tt]:before,
.wap-front [data-tt]:after {
  visibility: hidden;
  opacity: 0;
  pointer-events: none;
}

.wap-front [data-tt]:before {
  position: absolute;
  bottom: 150%;
  left: 50%;
  margin-bottom: -5px;
  margin-left: -48px;
  padding: 7px;
  width: auto;
  border-radius: 3px;
  content: attr(data-tt);
  text-align: center;
  font-size: 14px;
  line-height: 1.2;
}
.wap-front [data-tt]:after {
  position: absolute;
  bottom: 150%;
  left: 50%;
  margin-left: -5px;
  width: 0;
  border-right: 5px solid transparent;
  border-left: 5px solid transparent;
  content: " ";
  font-size: 0;
  line-height: 0;
  margin-bottom: -10px;
}

.wap-front [data-tt]:hover:before,
.wap-front [data-tt]:hover:after,
.wap-front [data-tt].hover:before,
.wap-front [data-tt].hover:after {
  visibility: visible;
  opacity: 1;
}


@media only screen and (max-width: 500px) {
  .wap-front.poppedup .wappo_module .wap-wid{
    min-width:100%;
  }
  .wap-abs{
    position: absolute;
    bottom: 0;
    width: 100%;
    max-height: 95%;
    overflow-y: scroll;
  }

  .wap-front.wexpanded .wap-bg
  .wap-front.br-fixed .wap-bg,
  .wap-front.wexpanded .wap-bg{
    position: fixed;
    height: 100%;
    width: 100%;
    background-color: rgba(0, 0, 0, .7);
    top: 0;
    z-index: 0;
    overflow-y: scroll;
  }
  .wap-front.br-fixed,
  .wap-front.wexpanded{
    width: 100%;
    margin: 0;
    position: fixed;
    left: 0;
    bottom: 0;
    overflow-y: scroll;
  }

  .wap-front.wexpanded{
    z-index: 999999999;
    height: 100%;
  }

  .wap-front.br-fixed .wbtn.wbtn-booking.wbtn-primary{
    margin: 0;
    width:100%;
    border-radius: .2rem .2rem 0 0;
  }

  .wap-front.br-fixed .wap-bf,
  .wap-front.br-fixed .wap-wid,
  .wap-front.wmobile .wap-bf,
  .wap-front.wmobile .wap-wid{
    max-width: 100%;
    position:relative;
    z-index: 1;
  }

  .wap-front.wmobile .wap-bf,
  .wap-front.wmobile .wap-wid{
    width:100%;
  }

  .wap-front.wmobile .wap-wid.step-button{
    width: auto;
  }
}
</style>