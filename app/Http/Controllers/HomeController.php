<?php

namespace AppHttpControllers;

use AppModelsService;
use IlluminateHttpRequest;

class HomeController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', true)->where('is_featured', true)->take(8)->get();
        return view('pages.home', compact('services'));
    }
}
