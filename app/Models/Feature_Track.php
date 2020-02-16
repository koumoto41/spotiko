<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Library\SpotifyApi;

/**
 * Class Feature_Track
 * @package App\Models
 * @property SpotifyApi $spotify_api_library
 */
class Feature_Track extends Model
{
    protected $spotify_api_library;
    protected $table = 'feature_track';
    protected $guarded = ['id'];
    protected $fillable = [
        'track_id',
        'danceability',
        'energy',
        'loudness',
        'mode',
        'speechiness',
        'acousticness',
        'instrumentalness',
        'liveness',
        'valence',
        'tempo',
        'time_signature'
    ];
    public $timestamps = true;
    Private $now;

    public function __construct()
    {
        $this->spotify_api_library = new SpotifyApi;
        $this->now = date('Y-m-d H:i:s');
    }

    public function exist_feature_track($track_id)
    {
        $data = DB::table($this->table)->where('track_id', $track_id)->first();
        if ($data) {
            return true;
        }
        return false;
    }

    public function insert_feature_track($feature_track)
    {
        $track_object = new Feature_Track;
        $track_object->fill($feature_track);
        $track_object->save();
    }
}
