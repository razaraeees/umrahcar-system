<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class VisaTypeController extends Controller
{
    public function index()
    {
        return view('admin.visa-types.index');
    }
}
