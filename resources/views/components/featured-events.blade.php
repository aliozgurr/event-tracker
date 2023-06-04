<div class="px-12 py-4 bg-slate-50">
    <h2 class="text-4xl font-extrabold dark:text-white text-center mb-4">Öne Çıkan Etkinlikler</h2>
    <div class="flex flex-col lg:flex-row gap-10 lg:gap-6 justify-between">
        @foreach($featuredEvents as $event)
            @include('components.partials.featured-event-card', [
                'image' => $event->image,
                'eventTitle' => $event->title,
                'date' => $event->date,
                'location' => $event->location,
                'platform' => $event->site,
                'eventLink' => $event->url
            ])
        @endforeach
    </div>

</div>
