<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Library\SpotifyApi;

/**
 * Class Feature_Track
 * @package App\Models
 * @property Track $track_model;
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
        $this->track_model = new Feature_Track();
        $this->now = date('Y-m-d H:i:s');
    }

    public function set_feature_track($track_array, $user_id)
    {
        foreach ($track_array as $row) {

            $track_detail = $this->track_model->get_track_by_spotify_id($row['track']['spotify_id']);

            if (!$track_detail) {
                continue;
            }

            $feature_track_array = [];
            $track_id = $track_detail->id;
            $track_spotify_id = $row['track']['spotify_id'];

            if ($this->exist_feature_track($track_id)) {
                continue;
            }

            $feature_track_detail = $this->spotify_api_library->get_audio_features_by_id($user_id, $track_spotify_id);

            $item = $feature_track_detail->audio_features[0];

            $feature_track_array['track_id'] = $track_id;
            $feature_track_array['danceability'] = $item->danceability;
            $feature_track_array['energy'] = $item->energy;
            $feature_track_array['loudness'] = $item->loudness;
            $feature_track_array['energy'] = $item->energy;
            $feature_track_array['mode'] = $item->mode;
            $feature_track_array['speechiness'] = $item->speechiness;
            $feature_track_array['acousticness'] = $item->acousticness;
            $feature_track_array['instrumentalness'] = $item->instrumentalness;
            $feature_track_array['liveness'] = $item->liveness;
            $feature_track_array['valence'] = $item->valence;
            $feature_track_array['tempo'] = $item->tempo;
            $feature_track_array['time_signature'] = $item->time_signature;

            $this->feature_track_model->insert_feature_track($feature_track_array);
        }

        echo '終了';
    }



















    private function exist_feature_track($track_id)
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
