<?php
return [
    'CLIENT_ID' => env('SPOTIFY_CLIENT_ID'),
    'CLIENT_SECRET' => env('SPOTIFY_CLIENT_SECRET'),
    'REDIRECT_URI' => 'http://localhost:1111/token',
    'scope' => ['user-top-read']
];
?>
