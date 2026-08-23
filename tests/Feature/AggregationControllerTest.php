<?php

use App\Domain\Spotify\SpotifyClientInterface;
use App\Infrastructure\Persistence\Eloquent\Models\User;

test('未認証のリクエストは拒否される', function () {
    $this->postJson('/api/aggregate/artist')->assertUnauthorized();
    $this->postJson('/api/aggregate/track')->assertUnauthorized();
});

test('アーティスト集計はSpotifyが失敗してもクラッシュせず正常応答する', function () {
    $this->actingAs(User::factory()->create());

    $spotifyClient = Mockery::mock(SpotifyClientInterface::class);
    $spotifyClient->shouldReceive('getMyTop')->once()->with(1, 'artists')
        ->andThrow(new Exception('Some Spotify error', 500));
    $spotifyClient->shouldReceive('checkRefreshToken')->once()->andReturnFalse();
    $this->app->instance(SpotifyClientInterface::class, $spotifyClient);

    $this->postJson('/api/aggregate/artist')
        ->assertOk()
        ->assertJson(['message' => 'アーティスト集計が終了しました']);
});

test('曲目集計はSpotifyが失敗してもクラッシュせず正常応答する', function () {
    $this->actingAs(User::factory()->create());

    $spotifyClient = Mockery::mock(SpotifyClientInterface::class);
    $spotifyClient->shouldReceive('getMyTop')->once()->with(1, 'tracks')
        ->andThrow(new Exception('Some Spotify error', 500));
    $spotifyClient->shouldReceive('checkRefreshToken')->once()->andReturnFalse();
    $this->app->instance(SpotifyClientInterface::class, $spotifyClient);

    $this->postJson('/api/aggregate/track')
        ->assertOk()
        ->assertJson(['message' => '曲目集計が終了しました']);
});
