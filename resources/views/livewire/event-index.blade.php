<div>
    <h2 class="text-4xl font-bold dark:text-white text-center mb-6">
        @if(request()->has('search')) {{ request()->get('search') }} için arama sonuçları @endif
    </h2>
    @foreach($events as $event)
        <a href="{{ $event->url }}" class="flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow md:flex-row md:max-w-xl hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 mb-8 lg:mx-auto lg:min-w-[968px]">
            <img class="object-cover w-full rounded-t-lg h-96 md:h-auto md:w-[18rem] md:rounded-none md:rounded-l-lg" src="{{ $event->image }}" alt="">
            <div class="flex flex-col justify-between p-4 leading-normal">
                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $event->title }}</h5>
                <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">{{ Str::limit(strip_tags($event->description, 140)) }}</p>
                <p class="mb-3 font-normal text-gray-500 dark:text-gray-400">{{ $event->location }}, {{ $event->date }}</p>
                <p class="mb-3 font-normal text-gray-500 dark:text-gray-400">Platform: {{ $event->site }}</p>
            </div>
        </a>
    @endforeach
    @if($loadMore)
        <div class="flex justify-center">
            <button type="button" wire:click="update()" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Load More</button>
        </div>
    @endif
</div>
