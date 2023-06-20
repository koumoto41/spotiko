<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;

final class GetSpotifyTokenRequest extends Request
{
    public function rules(): array
    {
        return [
            'code' => 'required',
            'user_id' => 'required',
        ];
    }
}

