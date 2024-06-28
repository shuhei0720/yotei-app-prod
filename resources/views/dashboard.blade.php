<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between">
                        <form method="POST" action="{{ route('teams.store') }}">
                            @csrf
                            <input type="text" name="name" placeholder="Team Name" class="border p-2">
                            <button type="submit" class="ml-2 bg-blue-500 text-white px-4 py-2">Create Team</button>
                        </form>

                        <form method="POST" action="{{ route('teams.join') }}">
                            @csrf
                            <input type="text" name="team_id" placeholder="Team ID" class="border p-2">
                            <button type="submit" class="ml-2 bg-green-500 text-white px-4 py-2">Join Team</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>