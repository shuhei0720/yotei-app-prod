<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            チーム
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('error'))
                        <div class="mb-4 text-red-600">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('teams.store') }}">
                        @csrf
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">チーム名</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">チームを作成</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('teams.join') }}" class="mt-6">
                        @csrf
                        <div>
                            <label for="team_id" class="block text-sm font-medium text-gray-700">チームIDで参加</label>
                            <input type="text" name="team_id" id="team_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">チームに参加</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>