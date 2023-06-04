<div>
    <h4 class="text-2xl font-bold dark:text-white min-h-[50px] mb-4">Yaklaşan Etkinlikler</h4>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
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
            @foreach($upcomingEvents as $event)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
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

</div>
