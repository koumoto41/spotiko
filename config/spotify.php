<?php
return [
    'CLIENT_ID' => env('SPOTIFY_CLIENT_ID'),
    'CLIENT_SECRET' => env('SPOTIFY_CLIENT_SECRET'),
    'REDIRECT_URI' => 'http://localhost/token',
    'scope' => ['user-top-read']
];
?>