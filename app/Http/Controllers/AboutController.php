<?php

namespace AppHttpControllers;

use IlluminateHttpRequest;

class AboutController extends Controller
{
    public function index()
    {
        return view('pages.about');
    }
}
