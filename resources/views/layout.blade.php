<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
</head>
    <body>
        <main>
            @include('components.navbar')

            @yield('content')

            @include('components.footer')
        </main>

        @livewireScripts

        <script>
            function search(e) {
                console.log('test')
                let query = document.getElementById('simple-search').value;
                window.location.href = '/events?search=' + query;
            }

            document.getElementById('simple-search').addEventListener('keyup', function (e) {
                if (e.key === 'Enter') {
                    search(e);
                }
            });
        </script>

        @stack('scripts')

        <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
    </body>
</html>
