<?php

// http://www.laravelsd.com/share/5f5iHy

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use \Models\Mship\Account;
use \Models\Mship\Account\Ban as AccountBan;

class PostmasterTemplatesRemoval extends Migration {

    public function up()
    {
        Schema::dropIfExists("sys_postmaster_template");

        Schema::create('messages_thread', function(Blueprint $table) {
            $table->bigIncrements('thread_id');
            $table->string('subject', 255);
            $table->boolean('read_only')->default(0);
            $table->timestamps();
        });

        Schema::create('messages_thread_post', function(Blueprint $table) {
            $table->bigIncrements('thread_post_id');
            $table->bigInteger('thread_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->smallInteger("status");
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('messages_thread_participant', function(Blueprint $table) {
            $table->bigIncrements('thread_participant_id');
            $table->bigInteger('thread_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->string('display_as', 255);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });


        // We need to convert the entire queue into the new messages schema.
        $pmQueue = DB::table("sys_postmaster_queue")->get();
        foreach($pmQueue as $pmq){
            try {
                // Create empty models and load sender and recip for later use.
                $thread = new App\Models\Messages\Thread();
                $post = new App\Models\Messages\Thread\Post();
                $sender = App\Models\Mship\Account::find(($pmq->sender_id > 0 ? $pmq->sender_id : 707070));
                $recipient = App\Models\Mship\Account::find($pmq->recipient_id);

                // Setup the thread.  Don't attach any relations yet.
                $thread->subject = $pmq->subject;
                $thread->read_only = true;
                $thread->save();

                // Has the message been read?
                $participantRead = ($pmq->status == App\Models\Sys\Postmaster\Queue::STATUS_OPENED ? $pmq->updated_at : null);
                $participantRead = ($participantRead == null && $pmq->status == App\Models\Sys\Postmaster\Queue::STATUS_CLICKED ? $pmq->updated_at : null);

                // Add participants to the thread.
                $thread->participants()->attach($sender, ["status" => App\Models\Messages\Thread\Participant::STATUS_OWNER]);
                $thread->participants()->attach($recipient, ["status" => App\Models\Messages\Thread\Participant::STATUS_OWNER, "read_at" => $participantRead]);

                // Build the post content.  Attach straight to the thread and the sender's account.
                $post->content = $pmq->body;
                $thread->posts()->save($post);
                $sender->messagePosts()->save($post);
            } catch(Exception $e){
                print "Something went wrong somewhere: ".$e->getMessage();
                return true;
            }
        }

        // Remove the queue.  Goodbye data #goneForever
        Schema::drop("sys_postmaster_queue");
    }

    public function down()
    {
        Schema::drop('messages_thread_participant');
        Schema::drop('messages_thread_post');
        Schema::drop('messages_thread');

        // We lose data in the migration, as such only the structure can be restored NOT the data contained within it.
        Schema::create("sys_postmaster_template", function($table) {
            $table->bigIncrements("postmaster_template_id")->unsigned();
            $table->string("section", 35);
            $table->string("area", 35);
            $table->string("action", 35);
            $table->string("subject", 200);
            $table->text("body");
            $table->smallInteger("priority")->default(\App\Models\Sys\Postmaster\Template::PRIORITY_MED);
            $table->boolean("secondary_emails")->default(0);
            $table->string("reply_to", 50);
            $table->boolean("enabled")->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(array("section", "area", "action"));
        });

        DB::table("sys_postmaster_template")->insert(array(
            ["section" => "mship", "area" => "account", "action" => "created", "subject" => 'Membership Account Created - CID {{{ $recipient->account_id }}}', "body" => "", "secondary_emails" => 0, "reply_to" => "", "enabled" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["section" => "mship", "area" => "security", "action" => "forgotten", "subject" => 'SSO Secondary Password Reset', "body" => "", "secondary_emails" => 0, "reply_to" => "", "enabled" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["section" => "mship", "area" => "security", "action" => "reset", "subject" => 'SSO Secondary Password Reset', "body" => "", "secondary_emails" => 0, "reply_to" => "", "enabled" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
        ));

        Schema::create("sys_postmaster_queue", function($table){
            $table->bigIncrements("postmaster_queue_id")->unsigned();
            $table->integer("recipient_id")->unsigned();
            $table->bigInteger("recipient_email_id")->unsigned();
            $table->integer("sender_id")->unsigned();
            $table->bigInteger("sender_email_id")->unsigned();
            $table->bigInteger("postmaster_template_id")->unsigned();
            $table->smallInteger("priority")->default(\App\Models\Sys\Postmaster\Template::PRIORITY_MED);
            $table->string("subject");
            $table->text("body");
            $table->text("data");
            $table->smallInteger("status")->default();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}