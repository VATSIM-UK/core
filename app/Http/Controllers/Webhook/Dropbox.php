<?php

namespace App\Http\Controllers\Webhook;

use App\Libraries\Dropbox as DropboxLibrary;
use Cache;
use Illuminate\Http\Request;

class Dropbox extends WebhookController
{
    public function getDropbox(Request $request)
    {
        // return the provided challenge
        $challenge = $request->input('challenge') or abort(404);

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

        $tags = '';
        $names = '';
        $paths = '';

        // process them
        foreach ($entries as $entry) {
            $tags .= $entry->{'.tag'}."\n";
            $names .= $entry->name."\n";
            $paths .= $entry->path_lower."\n";
        }

        // return nothing
        return response('');
    }
}
