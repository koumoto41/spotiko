<?php

namespace App\Http\Controllers;

use App\UseCases\Top\GetList;
use App\UseCases\Line\LineTest;

class DisplayController extends Controller
{
    public function getIndex(GetList $getList): string
    {
        return view('top', $getList());
    }

    public function lineTest(LineTest $lineTest): void
    {
        var_dump($lineTest());
    }
}
