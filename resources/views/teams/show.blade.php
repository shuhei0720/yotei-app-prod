<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $team->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div id="calendar" data-events="{{ json_encode($events) }}"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="eventModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 hidden z-50">
        <div class="bg-white p-4 rounded shadow-lg w-96">
            <form id="eventForm" method="POST" action="{{ route('events.store') }}">
                @csrf
                <input type="hidden" name="team_id" value="{{ $team->id }}">
                <div>
                    <label for="name">Event Name</label>
                    <input type="text" name="name" id="name" class="border p-2 w-full" list="event-names">
                </div>
                <div class="mt-2">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="border p-2 w-full">
                </div>
                <div class="mt-2">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="border p-2 w-full">
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="button" onclick="closeModal()" class="mr-2 bg-gray-500 text-white px-4 py-2">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2">Save</button>
                </div>
            </form>
        </div>
    </div>

    <datalist id="event-names"></datalist>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/calendar.js'])
</x-app-layout>