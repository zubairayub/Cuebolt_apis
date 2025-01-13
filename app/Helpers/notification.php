
<?php
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

if (!function_exists('send_push_notification')) {
    function send_push_notification($token, $title, $body, $data = [], $type)
    {
        app(App\Providers\FirebaseServiceProvider::class)->sendNotification($token, $title, $body, $data, $type);
    }
}
