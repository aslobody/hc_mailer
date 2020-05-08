$(function() {
  var text = '<div class="form-group">' +
    '<label for="captchaValidation" class="required" id="captchaError"><span class="field-name">To prove you are a person please enter today\'s date</span><span class="datepicker-format"> (YYYY-MM-DD)</span> <strong class="required">(required)</strong></label>' +
    '<input class="form-control" id="captchaValidation" name="captchaValidation" type="date" data-rule-dateISO="true" required />' +
    '</div>';
  $("#captcha").append(text);
  // validate today's date
  $("#captchaValidation").on("blur", function() {
    var str = $(this).val();
    var today = new Date().toISOString().slice(0, 10);
    //alert(str);
    if (str !== today) {
      $("#captchaError").append('<strong class="error"><span class="label label-danger"><span class="prefix">Error: </span>Please enter today\'s date</span></strong>');
    } else {
      $("#captchaError strong.error").remove();
    }
  });
});
