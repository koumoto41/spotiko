<?php

namespace App\Console\Commands\update;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Library\SpotifyApi;
use Exception;
use App\Models;

/**
 * Class FeatureTraks
 * @package App\Console\Commands\update
 * @property SpotifyApi $spotify_api_library
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
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        ["audio_features"]=>
//  array(1) {
//        [0]=>
//    object(stdClass)#697 (18) {
//    ["danceability"]=>
//      float(0.429)
//      ["energy"]=>
//      float(0.991)
//      ["key"]=>
//      int(11)
//      ["loudness"]=>
//      float(-4.03)
//      ["mode"]=>
//      int(1)
//      ["speechiness"]=>
//      float(0.207)
//      ["acousticness"]=>
//      float(0.000618)
//      ["instrumentalness"]=>
//      float(0.104)
//      ["liveness"]=>
//      float(0.368)
//      ["valence"]=>
//      float(0.645)
//      ["tempo"]=>
//      float(176.058)
//      ["type"]=>
//      string(14) "audio_features"
//        ["id"]=>
//      string(22) "5dt0dBcBTgGb7Gt5hNTG91"
//        ["uri"]=>
//      string(36) "spotify:track:5dt0dBcBTgGb7Gt5hNTG91"
//        ["track_href"]=>
//      string(56) "https://api.spotify.com/v1/tracks/5dt0dBcBTgGb7Gt5hNTG91"
//        ["analysis_url"]=>
//      string(64) "https://api.spotify.com/v1/audio-analysis/5dt0dBcBTgGb7Gt5hNTG91"
//        ["duration_ms"]=>
//      int(161680)
//      ["time_signature"]=>
//      int(4)

        $id = '5dt0dBcBTgGb7Gt5hNTG91';

        $features = $this->spotify_api_library->get_audio_features_by_id(self::USER_ID, $id);

        var_dump($features);


    }
}
