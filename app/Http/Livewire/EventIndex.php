<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Livewire\Component;

class EventIndex extends Component
{
    public $events;
    public $shownEventCount = 10;
    public $loadMore = true;

    public function mount()
    {
        $this->query();
    }

    public function render()
    {
        return view('livewire.event-index');
    }

    public function update()
    {
        $this->shownEventCount += 10;
        $this->query();
    }

    public function query()
    {
        $this->events = Event::query()
            ->whereNotNull('image')
            ->where('start_date', '>=', now())
            ->when(request()->get('city'), function ($query) {
                $query->where('city_slug', request()->get('city'));
            })
            ->when(request()->get('search'), function ($query) {
                $query->where('title', 'like', '%' . request()->get('search') . '%');
            });

        if ($this->shownEventCount > $this->events->count()) {
            $this->loadMore = false;
        }

        $this->events = $this->events->take($this->shownEventCount)->get();
    }
}
