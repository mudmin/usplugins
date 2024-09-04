<?php
function sendgrid($to, $subject, $body, $to_name = "", $options = []) {
    global $user, $us_url_root, $abs_us_root;

    if($to == "" || $subject == "" || $body == "") {
        logger($user->data()->id, "sendgrid", "FAILED: Attempted to send without all required fields");
        return "All fields are required";
    }
    $to = rawurldecode($to);
    global $db;
    $send = $db->query("SELECT * FROM plg_sendgrid")->first();
    if($to_name == "") { $to_name = $to; }

    require_once($abs_us_root.$us_url_root.'usersc/plugins/sendgrid/vendor/autoload.php');

    $email = new \SendGrid\Mail\Mail();
 
    $email->setFrom($send->from, $send->from_name);
 
    $email->setSubject($subject);
    $email->addTo($to, $to_name);
  
    if(isset($options['template_id'])) {
        $email->setTemplateId($options['template_id']);
        if(isset($options['dynamic_template_data'])) {
            foreach($options['dynamic_template_data'] as $key => $value) {
                $email->addDynamicTemplateData($key, $value);
            }
        }
    } else {
        $email->addContent("text/html", $body);
    }

    if(isset($options['attachments']) && is_array($options['attachments'])) {
        foreach($options['attachments'] as $attachment) {
            $email->addAttachment(
                $attachment['content'],
                $attachment['type'],
                $attachment['filename'],
                $attachment['disposition'],
                $attachment['content_id']
            );
        }
    }

    $sendgrid = new \SendGrid($send->key);
    try {
        $response = $sendgrid->send($email);
        if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
            return true;
        } else {
            logger($user->data()->id, "sendgrid", "ERROR: " . $response->body());
            return $response->body();
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
        logger($user->data()->id, "sendgrid", "ERROR $msg");
        return $e->getMessage();
    }
}