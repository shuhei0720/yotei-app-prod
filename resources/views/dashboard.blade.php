<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ホーム') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between space-y-4 sm:space-y-0 sm:space-x-4">
                        <form method="POST" action="{{ route('teams.store') }}" class="w-full sm:w-auto">
                            @csrf
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center">
                                <input type="text" name="name" placeholder="チーム名を入力してチームを新規作成" class="border p-2 flex-grow sm:flex-grow-0 sm:w-auto mb-2 sm:mb-0 sm:mr-2">
                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">チームを作成</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('teams.join') }}" class="w-full sm:w-auto">
                            @csrf
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center">
                                <input type="text" name="team_id" placeholder="チームIDを入力してチームに参加" class="border p-2 flex-grow sm:flex-grow-0 sm:w-auto mb-2 sm:mb-0 sm:mr-2">
                                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md">チームに参加</button>
                            </div>
                        </form>
                    </div>

                    <div class="mt-8">
                        <h3 class="font-semibold text-lg text-gray-800 leading-tight">所属チーム</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                            @foreach (Auth::user()->teams as $team)
                                <div class="relative">
                                    <a href="{{ route('teams.show', $team->id) }}" class="block bg-gray-100 p-4 rounded-lg shadow hover:bg-gray-200 transition">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-2">
                                                <span class="bg-gray-300 text-gray-700 px-2 py-1 rounded text-sm font-semibold">チームID: {{ $team->team_id }}</span>
                                                <span class="text-lg font-bold text-blue-600">{{ $team->name }}</span>
                                            </div>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-2 items-center">
                                            @foreach ($team->members as $member)
                                                <div class="flex items-center whitespace-nowrap">
                                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $member->color }};"></div>
                                                    <span class="ml-2 text-sm">{{ $member->name }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </a>
                                    <div class="absolute top-4 right-4 z-20">
                                        <form method="POST" action="{{ route('teams.leave') }}" onsubmit="return confirmLeaveTeam();">
                                            @csrf
                                            <input type="hidden" name="team_id" value="{{ $team->team_id }}">
                                            <button type="submit" class="bg-red-500 text-white px-2 py-1 text-xs rounded-md hover:bg-red-600">チームを抜ける</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmLeaveTeam() {
            return confirm('本当にチームを抜けますか？');
        }
    </script>
</x-app-layout>