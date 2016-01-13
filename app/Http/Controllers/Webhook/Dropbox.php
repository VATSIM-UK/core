<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Requests;
use App\Libraries\Dropbox as DropboxLibrary;
use Cache;
use Illuminate\Http\Request;

class Dropbox extends WebhookController
{
    public function getDropbox(Request $request)
    {
        // return the provided challenge
        $challenge = $request->input('challenge') OR abort(404);

        return $challenge;
    }

    public function postDropbox(Request $request)
    {
        // webhook body
        $body = $request->getContent();
        $data = json_decode($body);

        // obtain our latest cursor, or obtain one if it doesn't exist
        $cursor = Cache::rememberForever(DropboxLibrary::KEY_LATEST_CURSOR, function () {
            return $this->getCursor();
        });

        // get the changed entries
        $entries = DropboxLibrary::getUpdates($cursor);

        // process them
        foreach ($entries as $entry) {
            // do something
        }

        // return nothing
        return response('');
    }
}
