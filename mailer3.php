<?php

/* * ********************************************************************************************************************
  FORM EXAMPLE
  <div class="wb-frmvld">
    <form method="post" action="" accept-charset="UTF-8">
      <!-- ************************** FORM FIELDS START ************************ -->
      <div class="form-group">
        <label for="realname"><span class="field-name">Name :</span></label>
        <input name="realname" id="realname" size="30" class="form-control" />
      </div>
      <div class="form-group">
        <label for="email" class="required"><span class="field-name">Email :</span> <strong class="required">(required)</strong></label>
        <input type="email" name="email" id="email" size="30" class="form-control" required />
      </div>
      <div class="form-group">
        <label for="comments" class="required"><span class="field-name">Message:</span> <strong class="required">(required)</strong></label>
        <textarea cols="45" name="comments" id="comments" rows="10" required class="form-control"></textarea>
      </div>
      <!-- ************************** FORM FIELDS END ************************** -->
      <div>
        <!-- ************ REQUIRED AND VALIDATION FIELDS START ***************** -->
        <input type="hidden" name="recipient" value="RECIPIENT EMAIL" />

        <!-- if multiple recipients use these fields or type="checkbox"-->
        <input type="hidden" name="recipient[]" value="RECIPIENT EMAIL 1" />
        <input type="hidden" name="recipient[]" value="RECIPIENT EMAIL 2" />
        <!-- ******************************************************************* -->
        <!-- ******************************************************************* -->
        <input type="hidden" name="redirect" value="REDIRECT URL" />
        <input type="hidden" name="subject" value="MAIL SUBJECT" />
        <input type="hidden" name="required" value="field name 1|field label 1;field name 2|field label 2" />
        <input type="hidden" name="fields" value="field name 1|field label 1;field name 2|field label 2;field name 3|field label 3" />
        <!-- ******************************************************************* -->
        <!-- *************** custom reply messages if required ***************** -->
        <input type="hidden" name="replyMessageEn" value="ENGLISH AUTOREPLY MESSAGE" />
        <input type="hidden" name="replyMessageFr" value="FRENCH AUTOREPLY MESSAGE" />
        <!-- ******************************************************************* -->
        <!-- ****** set value to false if no autoreply message required ******** -->
        <input type="hidden" name="autoReplySend" value="false"/>
        <!-- ******************************************************************* -->
        <!-- ********* set value to text if no HTML email format required ****** -->
        <input type="hidden" name="emailFormatTo" value="text"/>
        <input type="hidden" name="emailFormatReplyTo" value="text"/>
        <!-- ******************************************************************* -->
        <!-- ******************************************************************* -->
        <!--**************** anti-spam validation fields *********************** -->
        <div class="form-group hidden">
          <label for="field1" class="required"><span class="field-name">field1</span>: <strong class="required">(required)</strong></label>
          <input class="form-control" type="text" id="field1" name="field1" maxlength="200" value="" placeholder="field1" required />
        </div>
        <div class="form-group hidden">
          <label for="field2" class="required"><span class="field-name">field2</span>: <strong class="required">(required)</strong></label>
          <input class="form-control" type="text" id="field2" name="field2" maxlength="200" value="" placeholder="field2" required />
        </div>
        <!-- *************** add ant-spam validation field names ****************-->
        <input type="hidden" name="validation" value="field1; field2">
        <!-- ******* redirect URL for anti-spam fields ************************* -->
        <input type="hidden" name="redirectValidation" value="REDIRECT URL VALIDATION" />
        <!-- ********************** basic captcha ****************************** -->
        <div id="captcha"></div>
        <!-- ******************************************************************* -->
        <!-- ************** REQUIRED AND VALIDATION FIELDS END ***************** -->
        <input type="submit" value="Submit" name="submit" class="btn btn-primary" />
      </div>
    </form>
  </div>
 * ********************************************************************************************************************* */
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

// set the autoreply messages
define("REPLY_EN", "This is an auto-generated e-mail; please do not reply. Your message has been received by the Web site administrator and is being forwarded to a subject-matter expert for consideration. You may be contacted if further information is needed.  Thank you.");
define("REPLY_FR", "Ce courriel est envoyé par un processus automatisé; veuillez ne pas répondre à ce courriel. Votre message a été reçu par l'administrateur du site Web et sera transmis à un expert en la matière pour évaluation. Vous pourriez être contacté si des informations supplémentaires sont requises. Merci.");

class mailer {

    // list of email domain names
    private $refererEmail = array('canada.ca', 'hc-sc.gc.ca', 'list.hc-sc.gc.ca', 'chemicalsubstanceschimiques.gc.ca', 'healthycanadians.gc.ca', 'canadiensensante.gc.ca', 'phac-aspc.gc.ca');
    // list of site domain namesnames
    private $refererSite = array('web.hc-sc.gc.ca', 'www.hc-sc.gc.ca', '205.193.190.11', 'dev.healthycanadians.gc.ca', 'dev.canadiensensante.gc.ca', 'www.healthycanadians.gc.ca', 'www.canadiensensante.gc.ca', 'healthycanadians.gc.ca', 'canadiensensante.gc.ca', '205.193.190.5', '205.193.190.7', 'health.canada.ca', 'sante.canada.ca');
    private $recipientEmail;
    private $replyToEmail;
    private $subject;
    // default email address for headers FROM, Reply-To
    private $noReplyDefault = 'no-reply-pas-repondre@hc-sc.gc.ca';

    private function refererDomain() {
        $serverName = $_SERVER['SERVER_NAME'];
        foreach ($this->refererSite as $val) {
            if ($serverName == $val)
            //if (strpos($serverName, $val) !== false)
                return true;
        }
    }

    private function allowEmailTo($email) {
        foreach ($this->refererEmail as $val) {
            if (strrpos($email, $val) !== false && eregi("^[_\.0-9a-zA-Z-]+@" . $val, $email))
                return true;
        }
    }

    private function validate_email($email) {
        return eregi("^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$", $email);
    }

    private function safe($name) {
        return (str_ireplace(array("%0a", "%0d", "bcc:", "cc:"), "", $name));
    }

    //email headers HTML
    private function mailHeadersHTML($mailTo, $replyTo) {
        $headers .= "Organization: Health Canada\r\n";
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $mailTo . "\r\n";
        $headers .= "Reply-To: " . $replyTo . "\r\n";
        $headers .= "Return-Path: " . $mailTo . "\r\n";
        $headers .= "X-Priority: 3\r\n";
        $headers .= "X-Mailer: PHP" . phpversion();
        $headers = $this->safe($headers);
        return $headers;
    }

    // basic email headers, plain text
    private function mailHeadersText($mailTo, $replyTo) {
        $headers .= "Organization: Health Canada\r\n";
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/plain;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $mailTo . "\r\n";
        $headers .= "Reply-To: " . $replyTo . "\r\n";
        $headers .= "Return-Path: " . $mailTo . "\r\n";
        $headers .= "X-Priority: 3\r\n";
        $headers .= "X-Mailer: PHP" . phpversion();
        $headers = $this->safe($headers);
        return $headers;
    }

    private function getSubject() {
        $subject = trim($_POST['subject']);
        if (isset($subject) && $subject !== '') {
            $subject = 're: ' . $subject;
            return $subject;
        }
    }

    private function getRecipientEmail() {
        $recipient = $_POST['recipient'];
        if (isset($recipient) && $recipient !== '') {
            return $recipient;
        }
    }

    private function getReplyToEmail() {
        $replyToEmail = trim(strip_tags($_POST['email']));
        if (isset($replyToEmail) && $replyToEmail !== '') {
            return $replyToEmail;
        } else {
            return FALSE;
        }
    }

    private function getAutoReplySend() {
        $autoReplySend = strtolower(trim($_POST['autoReplySend']));
        if (isset($autoReplySend) && $autoReplySend === 'false') {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // default email format is HTML
    private function getEmailFormatTo() {
        $emailFormatTo = strtolower(trim($_POST['emailFormatTo']));
        if (isset($emailFormatTo) && $emailFormatTo === 'text') {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // default autoreply email format is HTML
    private function getEmailFormatReplyTo() {
        $emailFormatReplyTo = strtolower(trim($_POST['emailFormatReplyTo']));
        if (isset($emailFormatReplyTo) && $emailFormatReplyTo === 'text') {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function getRedirect() {
        $redirect = trim($_POST['redirect']);
        if (isset($redirect) && $redirect !== '') {
            return $redirect;
        }
    }

    private function getAutoReplyEn() {
        $replyMessageEn = trim($_POST['replyMessageEn']);
        if (isset($replyMessageEn) && $replyMessageEn !== '') {
            return $replyMessageEn;
        } else {
            return FALSE;
        }
    }

    private function getAutoReplyFr() {
        $replyMessageFr = trim($_POST['replyMessageFr']);
        if (isset($replyMessageFr) && $replyMessageFr !== '') {
            return $replyMessageFr;
        } else {
            return FALSE;
        }
    }

    private function getFields() {
        $fields = $_POST['fields'];
        if (isset($fields) && $fields !== '') {
            return $fields;
        }
    }

    private function getRequired() {
        $required = $_POST['required'];
        if (isset($required) && $required !== '') {
            return $required;
        }
    }

    // build HTML email message
    private function buildMessageHTML() {
        $fields = explode(";", $this->getFields());
        //$message = '<html><body style="font-family:Arial, Helvetica, sans-serif;font-size:12px;">';
        foreach ($fields as $values) {
            $tmp = explode("|", $values);
            // check if the value is an array
            if (is_array($_POST[$tmp[0]])) {
                $message .= "<p><strong>" . trim($tmp[1]) . ":</strong></p>";
                $message .= "<ul>";
                foreach ($_POST[$tmp[0]] as $c) {
                    $c = trim($c);
                    if ($c !== '') {
                        $message .= "<li>" . $c . "</li>";
                    }
                }
                $message .= "</ul>";
            } else {
                $message .= "<p><strong>" . trim($tmp[1]) . ":</strong><br>" . trim(strip_tags($_POST[$tmp[0]])) . "</p>";
            }
        }
        return $message;
    }

    // build text email message
    private function buildMessageText() {
        $fields = explode(";", $this->getFields());
        foreach ($fields as $values) {
            $tmp = explode("|", $values);
            // check if the value is an array
            if (is_array($_POST[$tmp[0]])) {
                $message .= trim($tmp[1]) . ":" . "\r\n";
                foreach ($_POST[$tmp[0]] as $c) {
                    $c = trim($c);
                    if ($c !== '') {
                        $message .= "- " . $c . "\r\n";
                    }
                }
                $message .= "\r\n";
            } else {
                $message .= trim($tmp[1]) . ":\r\n" . trim(strip_tags($_POST[$tmp[0]])) . "\r\n\r\n";
            }
        }
        return $message;
    }

    // build reply email message HTML
    private function buildReplyMessHTML() {
        if ($this->getAutoReplyEn() === FALSE || $this->getAutoReplyFr() === FALSE) {
            $reply_message = "<p>" . REPLY_EN . "</p>";
            $reply_message .= "<hr>";
            $reply_message .= "<p>" . REPLY_FR . "</p>";
        } else {
            $reply_message = "<p>" . $this->getAutoReplyEn() . "</p>";
            $reply_message .= "<hr>";
            $reply_message .= "<p>" . $this->getAutoReplyFr() . "</p>";
        }
        $reply_message .= "<hr>";
        return $reply_message;
    }

    // build reply email message text
    private function buildReplyMessText() {
        if ($this->getAutoReplyEn() === FALSE || $this->getAutoReplyFr() === FALSE) {
            $reply_message = REPLY_EN . "\r\n";
            $reply_message .= "------------------------------------------------------";
            $reply_message .= "\r\n" . REPLY_FR . "\r\n";
        } else {
            $reply_message = $this->getAutoReplyEn() . "\r\n";
            $reply_message .= "------------------------------------------------------";
            $reply_message .= "\r\n" . $this->getAutoReplyFr() . "\r\n";
        }
        $reply_message .= "------------------------------------------------------" . "\r\n";
        return $reply_message;
    }

    //validate required fields
    public function validateRequired() {
        $required = explode(";", $this->getRequired());
        foreach ($required as $value) {
            $tmpReq = explode("|", $value);
            if (!isset($_POST[$tmpReq[0]])) {
                return FALSE;
            }
            if (is_string($_POST[$tmpReq[0]]) && trim($_POST[$tmpReq[0]]) == '') {
                return FALSE;
            }
        }
        return TRUE;
    }

    // send message to recepient
    private function sendMessage($value) {
        if ($this->allowEmailTo($value) && $this->refererDomain()) {
            if ($this->validate_email($value)) {
                // set to default no-reply@hc-sc.gc.ca if reply email is not set
                if ($this->replyToEmail === FALSE) {
                    $this->replyToEmail = $this->noReplyDefault;
                }
                // if email is in basic text format
                if (!$this->getEmailFormatTo()) {
                    $mail_message = $this->buildMessageText();
                    $headers = $this->mailHeadersText($this->noReplyDefault, $this->replyToEmail);
                } else {
                    // email in HTML format
                    $mail_message = '<html><body style="font-family:Arial, Helvetica, sans-serif;font-size:12px;">';
                    $mail_message .= $this->buildMessageHTML();
                    $mail_message .= '</body></html>';
                    $headers = $this->mailHeadersHTML($this->noReplyDefault, $this->replyToEmail);
                }
                mail($value, $this->subject, $mail_message, $headers);
            }
        }
    }

    // send reply message
    private function sendReplyMessage($value) {
        if ($this->validate_email($value)) {
            // if email is in basic text format
            if (!$this->getEmailFormatReplyTo()) {
                $reply_message = $this->buildReplyMessText();
                $reply_message .= $this->buildMessageText();
                $headers = $this->mailHeadersText($this->noReplyDefault, $this->noReplyDefault);
            } else {
                // email in HTML format
                $reply_message = '<html><body style="font-family:Arial, Helvetica, sans-serif;font-size:12px;">';
                $reply_message .= $this->buildReplyMessHTML();
                $reply_message .= $this->buildMessageHTML();
                $reply_message .= '</body></html>';
                $headers = $this->mailHeadersHTML($this->noReplyDefault, $this->noReplyDefault);
            }
            mail($value, $this->subject, $reply_message, $headers);
        }
    }

    public function sendMail() {
        $this->subject = $this->getSubject();
        $this->replyToEmail = $this->getReplyToEmail();
        $this->recipientEmail = $this->getRecipientEmail();
        // send emails to multiple recipints
        if (is_array($this->recipientEmail)) {
            foreach ($this->recipientEmail as $value) {
                $this->sendMessage($value);
            }
        }
        // send email to one recipient
        if (is_string($this->recipientEmail)) {
            $this->sendMessage($this->recipientEmail);
        }
        // send reply email
        if ($this->replyToEmail !== FALSE && $this->getAutoReplySend()) {
            $this->sendReplyMessage($this->replyToEmail);
        }
    }

    // get anti-spam validation field
     private function getValidation() {
         $validation = $_POST['validation'];
         if (isset($validation) && $validation !== '') {
             return $validation;
         }
     }
     // get ani-spam redirect field
     public function getRedirectValidation() {
         $redirectValidation = trim($_POST['redirectValidation']);
         if (isset($redirectValidation) && $redirectValidation !== '') {
             return $redirectValidation;
         }
     }
     // validate anti-spam validation fields
     public function validateValidationFields() {
         $validationFields = explode(";", $this->getValidation());
         foreach ($validationFields as $value) {
           $value = trim($value);
             if (!isset($_POST[$value])) {
                 return FALSE;
             }
             if (is_string($_POST[$value]) && trim($_POST[$value]) == '') {
                 return FALSE;
             }
         }
         return TRUE;
     }

}

if (isset($_POST['submit'])) {
    $myMailer = new mailer;
    if ($myMailer->validateRequired()) {
        $myMailer->sendMail();
        header('Location: ' . $myMailer->getRedirect());
    } else {
        print 'Some required fields are missing';
    }
}
