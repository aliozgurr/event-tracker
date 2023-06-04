<div>
    <div class="mb-4">
        @foreach($availableCities as $city)
            <button type="button" id="city" wire:click="filterByCity('{{ $city->city_slug }}')" class="@if($selected_city === strtolower($city->city_slug)) text-white bg-black hover:bg-black @else text-gray-900 bg-white @endif border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700 city-button @if($city->event_count === 1) hidden @endif">{{ $city->city }}</button>
        @endforeach
            <button type="button" id="show-more-cities" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Diğer Şehirler</button>
    </div>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">
                    <span class="sr-only"></span>
                </th>
                <th scope="col" class="px-6 py-3">
                    Etkinlik
                </th>
                <th scope="col" class="px-6 py-3">
                    Tarih
                </th>
                <th scope="col" class="px-6 py-3">
                    Şehir
                </th>
                <th scope="col" class="px-6 py-3">
                    <span class="sr-only"></span>
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($events as $event)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <td class="w-32 p-4">
                        <img src="{{ $event->image }}">
                    </td>
                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                        {{ $event->title }}
                    </td>
                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                        {{ $event->date }}
                    </td>
                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                        {{ $event->location }}
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ $event->url }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline" target="_blank">Detayları Gör</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if($pageCount > 1)
        <nav class="mt-5">
            <ul class="inline-flex items-center -space-x-px">
                @for ($i = 1; $i <= $pageCount; $i++)
                    <li class="col-span-1">
                        <a href="javascript:void(0)" wire:click="paginateEvents({{ $i }})" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">{{ $i }}</a>
                    </li>
                    @if($i == 8)
                        @break
                    @endif
                @endfor
            </ul>
        </nav>
    @endif
</div>
