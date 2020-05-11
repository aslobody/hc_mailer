$(function() {
  var textEn = '<div class="form-group">' +
    '<label for="captchaValidation" class="required" id="captchaError"><span class="field-name">To prove you are a person please enter today\'s date</span><span class="datepicker-format"> (YYYY-MM-DD)</span> <strong class="required">(required)</strong></label>' +
    '<input class="form-control" id="captchaValidation" name="captchaValidation" type="date" data-rule-dateISO="true" required />' +
    '</div>';
  var textFr = '<div class="form-group">' +
    '<label for="captchaValidation" class="required" id="captchaError"><span class="field-name">Pour prouver que vous êtes une personne, veuillez entrer la date d\'aujourd\'hui</span><span class="datepicker-format"> (YYYY-MM-DD)</span> <strong class="required">(obligatoire)</strong></label>' +
    '<input class="form-control" id="captchaValidation" name="captchaValidation" type="date" data-rule-dateISO="true" required />' +
    '</div>';
  var appendErrorEn = '<strong class="error"><span class="label label-danger"><span class="prefix">Error: </span>Please enter today\'s date</span></strong>';
  var appendErrorFr = '<strong class="error"><span class="label label-danger"><span class="prefix">Erreur : </span>Veuillez entrer la date du jour</span></strong>';
  var lang = $("html").attr("lang");
  if (lang === 'en') {
    $("#captcha").append(textEn);
  } else if (lang === 'fr') {
    $("#captcha").append(textFr);
  }
  // validate today's date
  $("#captchaValidation").on("blur", function() {
    var str = $(this).val();
    var today = new Date().toISOString().slice(0, 10);
    if (str !== today && lang === 'en') {
      $("#captchaError").append(appendErrorEn);
    } else if (str !== today && lang === 'fr') {
      $("#captchaError").append(appendErrorFr);
    } else {
      $("#captchaError strong.error").remove();
    }
  });
});
