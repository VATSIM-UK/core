<?php

namespace Models\Sys\Postmaster;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use \Models\Mship\Account;
use \Models\Mship\Account\Email as AccountEmail;
use \Models\Sys\Postmaster\Template;

class Queue extends \Models\aTimelineEntry {

    use SoftDeletingTrait;

    protected $table = "sys_postmaster_queue";
    protected $primaryKey = "postmaster_queue_id";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['postmaster_queue_id'];
    protected $attributes = ['status' => self::STATUS_PENDING];

    const STATUS_PENDING = 10;
    const STATUS_PARSED = 30;
    const STATUS_PARSE_ERROR = 35;
    const STATUS_DISPATCH_ERROR = 55;
    const STATUS_DISPATCHED = 50; // Deprecated const.
    const STATUS_DELIVERED = 60;
    const STATUS_OPENED = 63;
    const STATUS_CLICKED = 67;
    const STATUS_DROPPED = 91;
    const STATUS_BOUNCED = 92;
    const STATUS_SPAM = 93;
    const STATUS_UNSUBSCRIBED = 94;

    public function scopeOfStatus($query, $status) {
        return $query->where("status", "=", $status);
    }

    public function scopePending($query) {
        return $query->ofStatus(self::STATUS_PENDING);
    }

    public function scopeParsed($query) {
        return $query->ofStatus(self::STATUS_PARSED);
    }

    public function scopeDispatched($query) {
        return $query->ofStatus(self::STATUS_DISPATCHED);
    }

    public function scopeDelivered($query) {
        return $query->ofStatus(self::STATUS_DELIVERED);
    }

    public function scopeOpened($query) {
        return $query->ofStatus(self::STATUS_OPENED);
    }

    public function scopeClicked($query) {
        return $query->ofStatus(self::STATUS_CLICKED);
    }

    public function scopeDropped($query) {
        return $query->ofStatus(self::STATUS_DROPPED);
    }

    public function scopeBounced($query) {
        return $query->ofStatus(self::STATUS_BOUNCED);
    }

    public function scopeSpam($query) {
        return $query->ofStatus(self::STATUS_SPAM);
    }

    public function scopeUnsubscribed($query) {
        return $query->ofStatus(self::STATUS_UNSUBSCRIBED);
    }

    public function scopeAllDispatched($query){
        $dispatchedStati = [self::STATUS_DISPATCHED, self::STATUS_DELIVERED, self::STATUS_OPENED, self::STATUS_CLICKED, self::STATUS_DROPPED, self::STATUS_BOUNCED, self::STATUS_SPAM, self::STATUS_UNSUBSCRIBED];

        return $this->where("status", "IN", $dispatchedStati);
    }

    public function recipient() {
        return $this->belongsTo("\Models\Mship\Account", "recipient_id", "account_id");
    }

    public function recipientEmail() {
        return $this->belongsTo("\Models\Mship\Account\Email", "recipient_email_id", "account_email_id");
    }

    public function sender() {
        return $this->belongsTo("\Models\Mship\Account", "sender_id", "account_id");
    }

    public function senderEmail() {
        return $this->belongsTo("\Models\Mship\Account\Email", "sender_email_id", "account_email_id");
    }

    public function template() {
        return $this->belongsTo("\Models\Sys\Postmaster\Template", "postmaster_template_id", "postmaster_template_id");
    }

    public function getDisplayValueAttribute() {
        return array_get($this->attributes, "postmaster_queue_id").".".$this->template->display_value;
    }

    public function setDataAttribute($data) {
        $this->attributes['data'] = json_encode($data);
    }

    public function getDataAttribute($data) {
        return ($this->attributes['data'] ? json_decode($this->attributes['data']) : []);
    }

    public function setMessageIdAttribute($value){
        $this->attributes['message_id'] = strpos($value, "@") ? substr($value, 0, strpos($value, "@")) : $value;
    }

    public static function queue($postmasterTemplate, $recipient, $sender, $data) {
        // If the PostmasterTemplate isn't a class, we've been given the key.  Use it.
        if (!is_object($postmasterTemplate)) {
            $postmasterTemplate = \Models\Sys\Postmaster\Template::findFromKey($postmasterTemplate);
        }

        // Check the postmasterTemplate is real, first.
        if (!is_object($postmasterTemplate) OR ! $postmasterTemplate->exists) {
            return false;
        }

        // If the email isn't enabled, end it here!
        if (!$postmasterTemplate->enabled) {
            // TODO: Log.
            return false;
        }

        // Is the recipient an ID, or is it a model?
        if (is_numeric($recipient)) {
            $recipient = Account::find($recipient);
        } else {
            // Since it's a model, is it an email model? If so, we need to load the user
            if($recipient instanceof AccountEmail){
                $specifiedEmail = $recipient->account_email_id;

                $recipient = $recipient->account;
            } else {
                $specifiedEmail = 0;
            }
        }

        // Recipient loaded OK?
        if (!is_object($recipient) OR ! $recipient->exists) {
            return false;
        }

        // If we've not specified an email, let's just use that
        if(isset($specifiedEmail) && !empty($specifiedEmail)){
            $recipientEmailIDs = [$specifiedEmail];
        } else {
            // Get the recipient email ID
            $recipientEmailIDs = [];
            if ($recipient->primary_email) {
                $recipientEmailIDs[] = $recipient->primary_email->account_email_id;
            }

            // Do we want secondary emails too?
            if($postmasterTemplate->secondary_emails){
                foreach($recipient->secondary_email as $e){
                    $recipientEmailIDs[] = $e->account_email_id;
                }
            }
        }

        // Is the sender an ID, or is it a model?
        if (is_numeric($sender)) {
            $sender = Account::find($sender);
        }

        // Sender loaded OK?
        if (!is_object($sender) OR ! $sender->exists) {
            return false;
        }

        // Let's get the sender email address.
        $senderEmailID = 0;
        if ($sender->primary_email) {
            $senderEmailID = $sender->primary_email->account_email_id;
        } elseif ($postmasterTemplate->reply_to && $postmasterTemplate->reply_to != NULL && $postmasterTemplate->reply_to != "") {
            $senderEmailID = $sender->addEmail($postmasterTemplate->reply_to, 1, 0, true);
        } else {
            // If we can't get a sender email ID, we'll default to the system one.
            $senderEmailID = Account::find(VATUK_ACCOUNT_SYSTEM)->primary_email->account_email_id;
        }

        foreach($recipientEmailIDs as $recipientEmailID){
            $queue = new \Models\Sys\Postmaster\Queue();
            $queue->recipient_id = $recipient->account_id;
            $queue->recipient_email_id = $recipientEmailID;
            $queue->sender_id = $sender->account_id;
            $queue->sender_email_id = $senderEmailID;
            $queue->postmaster_template_id = $postmasterTemplate->postmaster_template_id;
            $queue->priority = $postmasterTemplate->priority;
            $queue->data = $data;
            $queue->save();

            // We always dispatch "immedate" emails straight away.
            if($postmasterTemplate->priority == Template::PRIORITY_NOW){
                $queue->parseAndSave();
                $queue->dispatch();
            }
        }
    }

    public function parse() {
        if (!$this) {
            return false;
        }

        // If this email isn't of NEW status, then we're not parsing!
        if ($this->status != self::STATUS_PENDING) {
            return false;
        }

        // Check that there's a template
        if (!$this->template OR is_null($this->template)) {
            return false;
        }


        $template = Template::find($this->postmaster_template_id);

        $templateData = [];
        $templateData["queue"] = $this;
        $templateData["recipient"] = $this->recipient;
        $templateData["sender"] = $this->sender;
        $templateData["data"] = $this->data;

        try {
            $this->subject = \StringView::make(["template" => $template->subject, "cache_key" => "S".$this->postmaster_queue_id, "updated_at" => 0], $templateData)->render();
            $this->body = \StringView::make(["template" => $template->body, "cache_key" => "B".$this->postmaster_queue_id, "updated_at" => 0], $templateData)->render();

            $this->status = self::STATUS_PARSED;
        } catch(Exception $e){ //Message failed for whatever reason.
            $this->status = self::STATUS_PARSE_FAILED;
        }

        return $this;
    }

    public function parseAndSave() {
        if (!$this) {
            return false;
        }

        $this->parse();
        $this->save();
    }

    public function dispatch() {
        if(!$this){
            return false;
        }

        // If it's not parsed, and is pending, parse it.
        if($this->status == self::STATUS_PENDING){
            $this->parseAndSave();
        }

        // If it's not yet parsed, sod off.
        if($this->status != self::STATUS_PARSED){
            return false;
        }

        // Let us dispatch!
        $dataSet = [];
        $dataSet["queue"] = $this;
        $dataSet["template"] = $this->template;
        $dataSet["sender"] = $this->sender;
        $dataSet["recipient"] = $this->recipient;
        $dataSet["emailContent"] = nl2br($this->body);
        \Mail::send("emails.default", $dataSet, function($message){
            $message->sender($this->sender_email->email);
            $message->from($this->sender_email->email, $this->sender->name);
            $message->to($this->recipient_email->email, $this->recipient->name);

            if($this->template->reply_to){
                $message->replyTo($this->template->reply_to);
            } else {
                $message->replyTo(Account::find(VATUK_ACCOUNT_SYSTEM)->primary_email->email);
            }

            $message->subject($this->subject);

            $this->message_id = $message->getId();
            $this->status = Queue::STATUS_DISPATCHED;
            $this->save();
        });
    }

}
