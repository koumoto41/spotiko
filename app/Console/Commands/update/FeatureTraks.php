<?php

namespace App\Console\Commands\update;

use Illuminate\Console\Command;
use App\Library\SpotifyApi;
use App\Models;
use Illuminate\Support\Facades\Artisan;
use Exception;

/**
 * Class FeatureTraks
 * @package App\Console\Commands\update
 * @property SpotifyApi $spotify_api_library
 * @property Models\Track $track_model
 * @property Models\Feature_Track $feature_track_model
 */
class FeatureTraks extends Command
{
    protected $signature = 'command:FeatureTracks';
    protected $description = '曲の特徴取得';

    protected $spotify_api_library;
    const USER_ID = 1;

    public function __construct(SpotifyApi $spotify_api_library)
    {
        parent::__construct();

        $this->spotify_api_library = $spotify_api_library;
        $this->track_model = new Models\Track();
        $this->feature_track_model = new Models\Feature_Track();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $track_all = $this->track_model->get_track_all();

        foreach ($track_all as $row) {

            $feature_track_array = [];
            $track_id = $row->id;
            $track_spotify_id = $row->spotify_id;

            if ($this->feature_track_model->exist_feature_track($track_id)) {
                continue;
            }

            try {
                $data = $this->spotify_api_library->get_audio_features_by_id(self::USER_ID, $track_spotify_id);
            } catch (Exception $e) {
                # tokenリフレッシュ判定
                $this->spotify_api_library->is_refresh_token($e, self::USER_ID);
                Artisan::call('command:FeatureTracks');
                exit;
            }

            $item = $data->audio_features[0];

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
}
