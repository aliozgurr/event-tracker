<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class EventList extends Component
{
    public $selected_city = 'istanbul';
    public $events;
    public $pageCount;

    public function mount()
    {
        $this->events = Event::query()
            ->whereNotNull('image')
            ->whereNotNull('city')
            ->where('start_date', '>=', now())
            ->where('city_slug', $this->selected_city)
            ->get();

        $this->pageCount = $this->events->count() / 10;

        $this->events = $this->events->forPage(1, 10);
    }

    public function render()
    {
        $availableCities = Event::query()
            ->select('city', 'city_slug', DB::raw('COUNT(*) as event_count'))
            ->whereNotNull('city')
            ->whereNot('city', '')
            ->groupBy('city', 'city_slug')
            ->orderByRaw('event_count DESC')
            ->get()
            ->take(50);

        return view('livewire.event-list', [
            'availableCities' => $availableCities,
        ]);
    }

    public function filterByCity($city)
    {
        $newData = Event::query()
            ->whereNotNull('image')
            ->whereNotNull('city')
            ->where('start_date', '>=', now())
            ->where('city_slug', $city)
            ->get();

        $this->selected_city = $city;
        $this->pageCount = $newData->count() / 10;
        $this->events = $newData->take(10);
    }

    public function paginateEvents($page)
    {
        $newData = Event::query()
            ->whereNotNull('image')
            ->whereNotNull('city')
            ->where('start_date', '>=', now())
            ->where('city_slug', $this->selected_city)
            ->get();

        $this->pageCount = $newData->count() / 10;

        $this->events = $newData->forPage($page, 10);
    }
}
