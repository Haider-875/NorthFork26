<?php

namespace App\Http\Controllers;

use IlluminateHttpRequest;

class AboutController extends Controller
{
    public function index()
    {
        return view('pages.about');
    }
}
