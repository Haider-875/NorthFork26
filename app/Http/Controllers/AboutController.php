<?php

namespace App\Http\Controllers;

use IlluminateHttpRequest;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        return view('pages.about');
    }
}
