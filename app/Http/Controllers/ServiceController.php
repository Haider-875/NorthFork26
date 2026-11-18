<?php

namespace AppHttpControllers;

use AppModelsService;
use IlluminateHttpRequest;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', true)->orderBy('sort_order')->get();
        return view('pages.services', compact('services'));
    }
}
