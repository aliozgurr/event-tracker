<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function getAvailableCities()
    {
        return Event::query()
            ->whereNotNull('city')
            ->groupBy('city', 'city_slug')
            ->orderByRaw('COUNT(*) DESC')
            ->get(['city', 'city_slug']);
    }
}
