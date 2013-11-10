<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Postmaster_Queue extends Model_Master {

    protected $_db_group = 'sys';
    protected $_table_name = 'postmaster_queue';
    protected $_table_columns = array(
        'id' => array('data_type' => 'int'),
        'recipient_id' => array('data_type' => 'int'),
        'sender_id' => array('data_type' => 'int'),
        'email_id' => array('data_type' => 'int'),
        'priority' => array('data_type' => 'smallint'),
        'subject' => array('data_type' => 'varchar'),
        'body' => array('data_type' => 'varchar'),
        'data' => array('data_type' => 'varchar'),
        'timestamp_queued' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'timestamp_parsed' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'timestamp_scheduled' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'timestamp_delayed' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'timestamp_sent' => array('data_type' => 'timestamp', 'is_nullable' => TRUE),
        'status' => array('data_type' => 'smallint'),
    );
    
    // fields mentioned here can be accessed like properties, but will not be referenced in write operations
    protected $_ignored_columns = array(
    );
    
    // Belongs to relationships
    protected $_belongs_to = array(
        'sender' => array(
            'model' => 'Account_Main',
            'foreign_key' => 'sender_id',
        ),
        'email' => array(
            'model' => 'Postmaster_Email',
            'foreign_key' => 'email_id',
        ),
        'recipient' => array(
            'model' => 'Account_Main',
            'foreign_key' => 'recipient_id',
        ),
    );
    
    // Has man relationships
    protected $_has_many = array(
    );
    
    // Has one relationship
    protected $_has_one = array(
    );
    
    // Validation rules
    public function rules(){
        return array(
        );
    }
    
    // Data filters
    public function filters(){
        return array(
        );
    }
    
    /**
     * Queue an email for dispatch.
     * 
     * NB: This function doesn't parse the email subject or body, it just queues the mail_id, recipient_id and sender_id.
     * 
     * @param string|int $email The email key or ID
     * @param Account_Main|object|int $recipient The object or ID of the recipient.
     * @param Account_Main|object|int $sender The object or ID of the sender.
     * @param array $data A data array for each of the extra values in the email.
     * @param boolean $inhibitParsing If set to TRUE the script won't parse the email straight away.
     */
    public function action_add($email, $recipient, $sender=null, $data=array(), $inhibitParsing=false){
        // Check the email is valid.
        if(is_numeric($email)){
            $email = ORM::factory("Postmaster_Email", $email);
        } else {
            $email = ORM::factory("Postmaster_Email")->where("key", "=", $email)->find();
        }
        
        // If it's not a valid email, return.
        if(!is_object($email) OR !$email->loaded()){
            // TODO: Log.
            return false;
        }
        
        // Recipient MUST be specified!
        if(is_numeric($recipient)){
            $recipient = ORM::factory("Account_Main", $recipient);
        }
        if(!$recipient->loaded()){
            // TODO: log.
            return false;
        }
        
        // Let's validate the sender.  If they don't validate, default to default user.
        if(is_numeric($sender)){
            $sender = ORM::factory("Account_Main", $email);
            
            if(!$sender->loaded()){
                $sender = null;
            }
        }
        if($sender == null){
            $sender = Kohana::$config->load('general')->get("system_user");
            $sender = ORM::factory("Account_Main", $sender);
        }
        
        // Now, let's just store it.
        $this->sender_id = $sender->id;
        $this->recipient_id = $recipient->id;
        $this->email_id = $email->id;
        $this->priority = $email->priority;
        $this->data = ((count($data) > 0) ? json_encode($data) : "");
        $this->timestamp_queued = gmdate("Y-m-d H:i:s");
        $this->status = Enum_System_Postmaster_Queue_Status::QUEUED;
        $this->save();
        
        // Do we want to inhibit parsing on this occasion?
        // If this is TRUE it's assumed CRON will pick up the slack and continue from here...
        if($inhibitParsing){
            return $this;
        }
        
        // From here, the cronjob will pick this email up and parse it until it's heart is content!
        return $this->action_Parse();
    }
    
    /**
     * Fetch the email contents for the given email and then parse it to be fully formed.
     * 
     * @return boolean|object Return the object on success, false otherwise.
     */
    public function action_Parse(){
        // We're only allowed to parse loaded objects, so tell the unloaded ones to sod off.
        if(!$this->loaded()){
            return false;
        }
        
        // ... We're also only parsing QUEUED emails!
        if($this->status != Enum_System_Postmaster_Queue_Status::QUEUED){
            return false;
        }
        
        // Build our list of possible replacements for this email.
        $replacements = array("signature" => "%{setting.system.postmaster.email.signature}");
        
        // ..replacements based on the data stored for THIS email specifically.
        if($this->data != ""){
            foreach(json_decode($this->data) as $rKey => $rValue){
                // Nested replacements.
                if(is_array($rValue)){
                    foreach($rValue as $r2Key => $r2Value){
                        $replacements[$rKey.".".$r2Key] = $r2Value;
                    }
                } else {
                    $replacements[$rKey] = $rValue;
                }
            }
        }
        
        // ..replacements based on the sender and recipient of this email.
        foreach(array("sender", "recipient") as $_rc){
            foreach(array("name_first", "name_last") as $cKey){
                $replacements[$_rc.".".$cKey] = $this->{$_rc}->get($cKey);
            }
        }
        
        // ..now get all the settings!
        foreach(ORM::factory("Setting_Main")->find_all() as $setting){
            $key = $setting->group;
            $key.= ".".$setting->area;
            $key.= ".".$setting->section;
            $key.= (($setting->key != "") ? ".".$setting->key : "");
            $replacements["setting.".$key] = $setting->value;
        }
        
        // Now, let's expand all these variables into the subject and body of the email.
        // We'll keep looking for matches!
        $replaceCounter = 0;
        $this->subject = $this->email->subject;
        while(preg_match_all("/\%\{(.*?)\}/", $this->subject, $matches) && $replaceCounter <= 5){
            foreach($matches[1] as $match){
                if(!isset($replacements[$match])){ continue; }
                $this->subject = str_replace("%{".$match."}", $replacements[$match], $this->subject);
            }
            $replaceCounter++;
        }
        
        $replaceCounter = 0;
        $this->body = $this->email->body;
        while(preg_match_all("/\%\{(.*?)\}/", $this->body, $matches) && $replaceCounter <= 5){
            foreach($matches[1] as $match){
                if(!isset($replacements[$match])){ continue; }
                $this->body = str_replace("%{".$match."}", $replacements[$match], $this->body);
            }
            $replaceCounter++;
        }
        
        // Let's just nullify any missing values at this point!
        $this->subject = preg_replace("/\%\{.*?\}/i", "#", $this->subject);
        $this->body = preg_replace("/\%\{.*?\}/i", "#", $this->body);
        
        // Set timestamps and status
        $this->timestamp_parsed = gmdate("Y-m-d H:i:s");
        $this->status = Enum_System_Postmaster_Queue_Status::PARSED;
        $this->save();
        
        // We've got this far, we've succeeded.  TAKE A BREAK!
        // Cronjob will take it from here.
        return $this;
    }
    
    /**
     * This function will send a specific email using whatever protocal is currently set.
     * 
     * @return boolean TRUE on success, FALSE otherwise.
     */
    public function action_dispatch(){
        // We're only allowed to parse loaded objects, so tell the unloaded ones to sod off.
        if(!$this->loaded()){
            return false;
        }
        
        // ... We're also only dispatching PARSED or DELAYED emails!
        if($this->status != Enum_System_Postmaster_Queue_Status::PARSED AND $this->status != Enum_System_Postmaster_Queue_Status::DELAYED){
            return false;
        }
        
        // Create the mail object.
        $email = Email::factory($this->subject, strip_tags($this->body));
        
        // Let's generate the email within a template!
        $htmlBody = View::factory("Email/".$this->email->getTemplate()."/".$this->email->getLayout());
        $htmlBody->set("content", nl2br($this->body));
        $htmlBody->set("_key", $this->email->key);
        $htmlBody->set("_id", $this->id);
        $htmlBody->set("_template", $this->email->getTemplate());
        $htmlBody->set("_layout", $this->email->getLayout());
        $htmlBody = $htmlBody->render();
        $email->message($htmlBody, "text/html"); // HTML VERSION!
        
        // Set the to's, from's and reply_to's before sending!
        $email->to(strval($this->recipient->emails->get_active_primary()), strval($this->recipient->name_first." ".$this->recipient->name_last));
        $email->from(strval($this->sender->emails->get_active_primary()), strval($this->sender->name_first." ".$this->sender->name_last));
        if($this->email->reply_to == ""){
        $email->from(strval($this->sender->emails->get_active_primary()), strval($this->sender->name_first." ".$this->sender->name_last));
        } else {
            $email->reply_to(strval($this->email->reply_to));
        }
        
        // 3...2....1 LIFT OFF!
        if($email->send()){
            $this->timestamp_sent = gmdate("Y-m-d H:i:s");
            $this->status = Enum_System_Postmaster_Queue_Status::SENT;
            $this->save();
            return true;
        } else {
            $this->timestamp_delayed = gmdate("Y-m-d H:i:s");
            $this->status = Enum_System_Postmaster_Queue_Status::DELAYED;
            $this->save();
            return false;
        }
    }
}

?>