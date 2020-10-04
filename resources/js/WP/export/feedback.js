jQuery(function($){
    
  function wappo_feedback(){
      var deactivate_link = document.getElementById('deactivate-wappointment')
      var newDiv = document.createElement("div");
      newDiv.setAttribute("id", "wappo-feedback");
      newDiv.innerHTML = '<div id="wappo-feedback-content" >'+
                              '<div class="form-container">'+
                                  '<h1>Help us improve Wappointment!</h1>'+
                                  '<div>'+
                                      '<h2>We are sorry to see you go... please let us know why are you deactivating the plugin:</h2>'+
                                      '<form id="wappo-feed-form"><div class="wappo-field-row"><label><input type="radio" name="reason" value="0"> I couldn\'t install it</label></div>'+
                                      '<div class="wappo-field-row"><label><input type="radio" name="reason" value="1"> It\'s missing a feature I need</label></div>'+
                                      '<div class="wappo-field-row"><label><input type="radio" name="reason" value="2"> I have no use for it anymore</label></div>'+
                                      '<div class="wappo-field-row"><label><input type="radio" name="reason" value="3"> Other reason</label></div>'+
                                      '<div class="wappo-field-row reason-details wap-hidden"><label><textarea id="reason-details" name="details" placeholder="Please specify"></textarea></label></div></form>'+
                                  '</div>'+
                                  '<div class="wappo-feed-buttons">'+
                                      '<button id="wappo-feedback-cancel" class="button">Cancel</button> '+
                                      '<button id="wappo-feedback-confirm" class="button button-primary">Confirm & Deactivate</button>'+
                                  '</div>'+
                                  '<div class="wappo-feed-buttons">'+
                                      '<a id="wappo-feedback-skip" href="javascript:;">Skip and Deactivate</a> '+
                                  '</div>'+
                              '</div>'+
                              '<div class="wappo-loader wap-hidden"></div>'
                          '</div>';
      document.body.appendChild(newDiv);
      deactivate_link.addEventListener('click', wappo_open_feedback);
      document.getElementById('wappo-feedback-confirm').addEventListener('click', wappo_confirm_feedback);
      document.getElementById('wappo-feedback-cancel').addEventListener('click', wappo_close_feedback);
      document.getElementById('wappo-feedback-skip').addEventListener('click', wappo_skip_feedback);
      
      $( "input[name='reason']" ).change(function(e) {
          switch(e.target.value){
              case '1':
                  $( "#reason-details" ).attr('placeholder', 'Which feature do you need?');
                  $('.reason-details').removeClass('wap-hidden');
                  $('.reason-details textarea').val('');
                  break;
              case '3':
                  $( "#reason-details" ).attr('placeholder', 'Please specify');
                  $('.reason-details').removeClass('wap-hidden');
                  $('.reason-details textarea').val('');
                  break;
              default:
                  $('.reason-details').addClass('wap-hidden');
          }
      });
      
      
  }
  function wappo_get_form_data(){
      var form = $('#wappo-feed-form').serializeArray()
      var data_form = {}
      for (let i = 0; i < form.length; i++) {
          data_form[form[i].name] = form[i].value
      }
      return data_form
  }

  function wappo_confirm_feedback(e){
      var url = window.apiWappointment.root +'wappointment/v1/send_feedback';
      $('.wappo-loader').removeClass('wap-hidden');
      $('.form-container').hide();
      
      $.ajax({
          type: "POST",
          url: url,
          data: wappo_get_form_data(),
          dataType:'json',
          headers:{
              'X-WP-Nonce': window.apiWappointment.nonce
          }
      })
      .always(function(data) {
          $('.wappo-loader').addClass('wap-hidden');
          $('.form-container').show();
          //close
          wappo_close_feedback()
          //deactivate
          wappo_deactivate()
      });
        
  }


    
  function wappo_open_feedback(e){
      document.body.classList.add('wappo-modal-on');
      document.getElementById('wappo-feedback').classList.add('show');
      e.preventDefault();
  }
  function wappo_close_feedback(e){
      document.body.classList.remove('wappo-modal-on');
      document.getElementById('wappo-feedback').classList.remove('show');
  }
  function wappo_skip_feedback(e){
      wappo_close_feedback();
      wappo_deactivate();
  }
  function wappo_deactivate(){
      window.location = document.getElementById('deactivate-wappointment').getAttribute('href');
  }
  wappo_feedback();
});

