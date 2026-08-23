<?php

use App\Infrastructure\Persistence\Eloquent\Models\User;

test('ユーザーはログインして自分のプロフィールを取得できる', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()->assertJsonPath('data.email', $user->email);

    $this->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

test('パスワードが間違っているとログインに失敗する', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertUnprocessable();
});

test('未認証のリクエストは拒否される', function () {
    $this->getJson('/api/user')->assertUnauthorized();
});
