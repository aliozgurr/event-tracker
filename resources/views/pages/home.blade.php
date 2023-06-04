@extends('layout')

@section('content')
    @include('components.featured-events')

    <div class="p-12 grid grid-cols-1 lg:grid-cols-6 gap-8">
        <div class="col-span-1 lg:col-span-4">
            @include('components.horizontal-event-list')
        </div>
        <div class="col-span-1 lg:col-span-2">
            @include('components.upcoming-events-list')
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('show-more-cities').addEventListener('click', function (e) {
            e.preventDefault();
            Array.from(document.getElementsByClassName('city-button')).forEach(function (element) {
                element.classList.remove('hidden');
            });
            document.getElementById('show-more-cities').classList.add('hidden');
        });
    </script>
@endpush
