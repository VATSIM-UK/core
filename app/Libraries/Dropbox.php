<?php

namespace App\Libraries;

use Cache;
use stdClass;

/**
 * Class Dropbox.
 *
 * @todo add error handling for API requests
 * @todo add error handling for API responses
 */
class Dropbox
{
    const KEY_LATEST_CURSOR = 'DROPBOX_ACCESS_TOKEN';

    protected static function apiRequest($endpoint, $parameters)
    {
        $endpoint = trim($endpoint, '/');

        $ch = curl_init("https://api.dropboxapi.com/2/$endpoint");
        curl_setopt($ch, CURLOPT_POST, count($parameters));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.env('DROPBOX_ACCESS_TOKEN'),
            'Content-Type: application/json',
        ]);

        $response = new stdClass();
        $response->data = json_decode(curl_exec($ch));
        $response->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $response;
    }

    public static function getLatestCursor()
    {
        $fields = [
            'path' => '',
            'recursive' => true,
            'include_media_info' => false,
            'include_deleted' => false,
        ];

        $response = self::apiRequest('/files/list_folder/get_latest_cursor', $fields);

        return $response->data->cursor;
    }

    public static function getUpdates($cursor)
    {
        $fields = [
            'cursor' => $cursor,
        ];

        $response = self::apiRequest('/files/list_folder/continue', $fields);

        Cache::forever(self::KEY_LATEST_CURSOR, $response->data->cursor);

        return $response->data->entries;
    }
}
