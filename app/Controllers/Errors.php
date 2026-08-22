<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Errors extends BaseController
{
    public function accessDenied()
    {
        return view('errors/access_denied');
    }
}