<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredEvents = Event::query()
            ->whereNotNull('image')
            ->whereNotNull('city')
            ->inRandomOrder(rand(1, 10))
            ->limit(4)
            ->get();

        $availableCities = $this->getAvailableCities();

        $upcomingEvents = Event::query()
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        return view('pages.home', [
            'featuredEvents' => $featuredEvents,
            'availableCities' => $availableCities,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
