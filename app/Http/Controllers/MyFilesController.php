<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class MyFilesController extends Controller
{
    /**
     * Display the My Files page.
     *
     * @return \Inertia\Inertia
     */
    public function index()
    {
        return Inertia::render('MyFiles');
    }
}
