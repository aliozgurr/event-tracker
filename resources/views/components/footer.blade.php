<footer class="bg-slate-100 dark:bg-gray-900">
    <div class="mx-auto w-full max-w-screen-xl p-4 py-6 lg:py-8">
        <div class="md:flex md:justify-between">
            <div class="mb-6 md:mb-0">
                <a href="" class="flex items-center">
                    <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">Event Tracker</span>
                </a>
            </div>
            <div class="grid grid-cols-2 gap-8 sm:gap-12 sm:grid-cols-4">
                <div>
                    <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Etkinlikler</h2>
                    <ul class="text-gray-600 dark:text-gray-400 font-medium">
                        <li class="mb-4">
                            <a href="{{ route('events') }}" class="hover:underline">Tüm Etkinlikler</a>
                        </li>
                    </ul>
                </div>
                <!--
                <div>
                    <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Kategoriler</h2>
                    <ul class="text-gray-600 dark:text-gray-400 font-medium">
                        <li class="mb-4">
                            <a href="" class="hover:underline ">Müzik</a>
                        </li>
                        <li class="mb-4">
                            <a href="" class="hover:underline">Tiyatro</a>
                        </li>
                        <li>
                            <a href="" class="hover:underline">Stand-up</a>
                        </li>
                    </ul>
                </div>
                !-->
                <div>
                    <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Şehirler</h2>
                    <ul class="text-gray-600 dark:text-gray-400 font-medium">
                        <li class="mb-4">
                            <a href="{{ route('events') }}?city=istanbul" class="hover:underline ">Istanbul</a>
                        </li>
                        <li class="mb-4">
                            <a href="{{ route('events') }}?city=dubai" class="hover:underline">Dubai</a>
                        </li>
                        <li class="mb-4">
                            <a href="{{ route('events') }}?city=london" class="hover:underline">London</a>
                        </li>
                        <li class="mb-4">
                            <a href="{{ route('events') }}?city=berlin" class="hover:underline">Berlin</a>
                        </li>
                        <li class="mb-4">
                            <a href="{{ route('events') }}?city=paris" class="hover:underline">Paris</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Diğer Sayfalar</h2>
                    <ul class="text-gray-600 dark:text-gray-400 font-medium">
                        <li class="mb-4">
                            <a href="#" class="hover:underline">Hakkında</a>
                        </li>
                        <li class="mb-4">
                            <a href="#" class="hover:underline">İletişim</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
