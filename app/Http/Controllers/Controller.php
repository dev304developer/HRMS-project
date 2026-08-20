<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Enables $this->authorize(...) / authorizeResource() in child controllers.
    use AuthorizesRequests;
}
