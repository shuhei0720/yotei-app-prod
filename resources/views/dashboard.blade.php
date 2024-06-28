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
                    <div class="flex flex-col sm:flex-row justify-between space-y-4 sm:space-y-0 sm:space-x-4">
                        <form method="POST" action="{{ route('teams.store') }}" class="w-full sm:w-auto">
                            @csrf
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center">
                                <input type="text" name="name" placeholder="Team Name" class="border p-2 flex-grow sm:flex-grow-0 sm:w-auto mb-2 sm:mb-0 sm:mr-2">
                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Create Team</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('teams.join') }}" class="w-full sm:w-auto">
                            @csrf
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center">
                                <input type="text" name="team_id" placeholder="Team ID" class="border p-2 flex-grow sm:flex-grow-0 sm:w-auto mb-2 sm:mb-0 sm:mr-2">
                                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md">Join Team</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>