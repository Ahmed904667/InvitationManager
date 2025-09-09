<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Show the help and support page.
     */
    public function support()
    {
        return view('help.support');
    }
}
