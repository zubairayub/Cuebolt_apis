
<?php
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

if (!function_exists('send_push_notification')) {
    function send_push_notification(array $tokens, $title, $body, $data = [], $type)
    {
        app(App\Providers\FirebaseServiceProvider::class)->sendNotification($tokens, $title, $body, $data, $type);
    }
}
