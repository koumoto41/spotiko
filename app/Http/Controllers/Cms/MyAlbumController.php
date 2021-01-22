<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\My_Album;

class MyAlbumController extends Controller
{
    private $my_album_model;

    public function __construct()
    {
        $this->my_album_model = new My_Album();
    }

    public function my_album_list()
    {
        echo 'aaaaaaa';
    }
}
