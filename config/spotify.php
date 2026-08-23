<?php
return [
    'CLIENT_ID' => env('SPOTIFY_CLIENT_ID'),
    'CLIENT_SECRET' => env('SPOTIFY_CLIENT_SECRET'),
    'REDIRECT_URI' => env('SPOTIFY_REDIRECT_URI', env('APP_URL', 'http://localhost') . '/api/spotify/callback'),
    'scope' => ['user-top-read']
];
?>