<?php

namespace Models\Sys\Postmaster;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use \Models\Mship\Account\Account;

class Queue extends \Models\aTimelineEntry {

    use SoftDeletingTrait;

    protected $table = "sys_postmaster_queue";
    protected $primaryKey = "postmaster_queue_id";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['postmaster_queue_id'];
    protected $attributes = ['status' => self::STATUS_PENDING];

    const STATUS_PENDING = 10;
    const STATUS_PARSED = 30;
    const STATUS_SENT = 90;
    const STATUS_DELAYED = 98;
    const STATUS_REJECTED = 99;

    public function scopeOfStatus($query, $status) {
        return $query->where("status", "=", $status);
    }

    public function scopePending($query) {
        return $query->whereStatus(self::STATUS_PENDING);
    }

    public function scopeParsed($query) {
        return $query->whereStatus(self::STATUS_PARSED);
    }

    public function scopeSent($query) {
        return $query->whereStatus(self::STATUS_SENT);
    }

    public function scopeDelayed($query) {
        return $query->whereStatus(self::STATUS_DELAYED);
    }

    public function scopeRejected($query) {
        return $query->whereStatus(self::STATUS_REJECTED);
    }

    public function recipient() {
        return $this->belongsTo("\Models\Mship\Account\Account", "recipient_id", "account_id");
    }

    public function recipientEmail() {
        return $this->belongsTo("\Models\Mship\Account\Email", "recipient_email_id", "account_email_id");
    }

    public function sender() {
        return $this->belongsTo("\Models\Mship\Account\Account", "sender_id", "account_id");
    }

    public function senderEmail() {
        return $this->belongsTo("\Models\Mship\Account\Email", "sender_email_id", "account_email_id");
    }

    public function template() {
        return $this->belongsTo("\Models\Sys\Postmaster\Template", "postmaster_template_id", "postmaster_template_id");
    }

    public function getDisplayValueAttribute() {
        return "SOME GENERIC EMAIL ENTRY - NEEDS CHANGING.";
    }

    public function setDataAttribute($data) {
        $this->attributes['data'] = json_encode($data);
    }

    public function getDataAttribute($data) {
        return ($this->attributes['data'] ? json_decode($this->attributes['data']) : array());
    }

    public static function queue($postmasterTemplate, $recipient, $sender, $data) {
        \Log::info("Queue::queue::" . __LINE__);
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
        }

        // Recipient loaded OK?
        if (!is_object($recipient) OR ! $recipient->exists) {
            return false;
        }

        // Get the recipient email ID
        $recipientEmailID = 0;
        if ($recipient->primary_email) {
            $recipientEmailID = $recipient->primary_email->account_email_id;
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

        $queue = new \Models\Sys\Postmaster\Queue();
        $queue->recipient_id = $recipient->account_id;
        $queue->recipient_email_id = $recipientEmailID;
        $queue->sender_id = $sender->account_id;
        $queue->sender_email_id = $senderEmailID;
        $queue->postmaster_template_id = $postmasterTemplate->postmaster_template_id;
        $queue->priority = $postmasterTemplate->priority;
        $queue->data = $data;
        $queue->save();
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


        $this->subject = \DbView::make($this->template)
                                ->field("subject")
                                ->with("queue", $this)
                                ->with("recipient", $this->recipient)
                                ->with("sender", $this->sender)
                                ->with("data", $this->data)
                                ->render();

        $this->body = \DbView::make($this->template)
                             ->field("body")
                             ->with("queue", $this)
                             ->with("recipient", $this->recipient)
                             ->with("sender", $this->sender)
                             ->with("data", $this->data)
                             ->render();

        $this->status = self::STATUS_PARSED;
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
        $dataSet = array();
        $dataSet["queue"] = $this;
        $dataSet["template"] = $this->template;
        $dataSet["sender"] = $this->sender;
        $dataSet["recipient"] = $this->recipient;
        $dataSet["emailContent"] = $this->body;
        \Mail::send("emails.default", $dataSet, function($message){
            $message->sender($this->sender_email->email);
            $message->from($this->sender_email->email, $this->sender->name);
            $message->to($this->recipient_email->email, $this->recipient->name);

            // Does the user also want it to secondary emails?
            if($this->template->secondary_emails){
                foreach($this->recipient->secondary_email as $e){
                    $message->cc($e->email, $this->recipient->name." (SECONDARY ADDRESS)");
                }
            }

            if($this->template->reply_to){
                $message->replyTo($this->template->reply_to);
            } else {
                $message->replyTo(Account::find(VATUK_ACCOUNT_SYSTEM)->primary_email->email);
            }

            $message->subject = $this->subject;

            $this->status = Queue::STATUS_SENT;
            $this->save();
        });
    }

}
