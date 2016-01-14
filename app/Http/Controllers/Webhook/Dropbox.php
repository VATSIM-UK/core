<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Requests;
use App\Libraries\Dropbox as DropboxLibrary;
use App\Libraries\Slack as SlackLibrary;
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
            return DropboxLibrary::getLatestCursor();
        });

        // get the changed entries
        $entries = DropboxLibrary::getUpdates($cursor);

        $fields = [];
        // process them
        foreach ($entries as $entry) {
            $fields = [
                ['Tag:' => $entry->{'.tag'}],
                ['File name:' => $entry->name],
                ['Path:' => $entry->path_lower],
            ];
        }

        SlackLibrary::sendMessage(__FILE__, sprintf('%s Dropbox files have been changed', count($entries)), $fields);

        // return nothing
        return response('');
    }
}
