$(function(){
  var text = '<div class="form-group">'+
                '<label for="captchaValidation" class="required"><span class="field-name">To prove you are a person please enter today\'s date</span><span class="datepicker-format"> (YYYY-MM-DD)</span> <strong class="required">(required)</strong></label>'+
                '<input class="form-control" id="captchaValidation" name="captchaValidation" type="date" data-rule-dateISO="true" required />'+
              '</div>';
  $("#captcha").append(text);
});
