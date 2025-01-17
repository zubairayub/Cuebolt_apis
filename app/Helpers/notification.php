<?php
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

if (!function_exists('send_push_notification')) {
    function send_push_notification(array $tokens, $title, $body, $data = [], $type)
    {
        app(App\Providers\FirebaseServiceProvider::class)->sendNotification($tokens, $title, $body, $data, $type);


    }
}



if (!function_exists('register_user_firebase')) {
    function register_user_firebase($email, $password, $displayName = null)
    {

     //   app(App\Providers\FirebaseServiceProvider::class)->createUser($email, $password, $displayName = null);
    }
}


if (!function_exists('register_user_firestore')) {
    function register_user_firestore($userId,$username,$email,$profile_pictue)
    {
        

        app(App\Providers\FirebaseServiceProvider::class)->addUserToFirestore($userId,$username,$email,$profile_pictue);
    }
}
