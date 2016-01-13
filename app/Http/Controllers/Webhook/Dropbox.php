<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Webhook\WebhookController;
use App\Http\Requests;
use Illuminate\Http\Request;

/**
 * INDEV: Class does not code suitable for production.
 *
 * @package App\Http\Controllers\Webhook
 * @todo Have webhook queue a job to process the data
 * @todo Add error handling for API requests
 */
class Dropbox extends WebhookController
{
    public function getDropbox(Request $request)
    {
        $challenge = $request->input('challenge') OR abort(404);

        return $challenge;
    }

    public function postDropbox(Request $request)
    {
        $body = $request->getContent();
        $data = json_decode($body);

        /*
         * GET CURSOR -- move to own method
         */

        $fields = [
            'path' => '/',
            'recursive' => true,
            'include_media_info' => false,
            'include_deleted' => false,
        ];

        /*$post_data = '';
        foreach ($fields as $key => $value) {
            $post_data .= "$key=$value&";
        }
        rtrim($post_data, '&');*/

        $post_data = http_build_query($fields);

        // get latest cursor
        $curl = curl_init('https://api.dropboxapi.com/2/files/list_folder/get_latest_cursor');
        curl_setopt($curl, CURLOPT_POST, count($fields));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . env('DROPBOX_ACCESS_TOKEN'),
            'Content-Type: application/json',
        ]);

        $cursor = json_decode(curl_exec($curl))->cursor;
        curl_close($curl);

        /*
         * GET UPDATES -- move to own method
         */

        $fields = [
            'cursor' => $cursor,
            'recursive' => true,
            'include_media_info' => false,
            'include_deleted' => false,
        ];

        $post_data = http_build_query($fields);

        // get latest cursor
        $curl = curl_init('https://api.dropboxapi.com/2/files/list_folder/continue');
        curl_setopt($curl, CURLOPT_POST, count($fields));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . env('DROPBOX_ACCESS_TOKEN'),
            'Content-Type: application/json',
        ]);

        $entries = json_decode(curl_exec($curl))->entries;
        curl_close($curl);

        foreach ($entries as $entry) {
            \Log::info($entry->name);
        }

        return response();
    }
}
