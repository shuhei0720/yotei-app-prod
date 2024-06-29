<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $team->name }}
            </h2>
            <form method="POST" action="{{ route('teams.leave') }}">
                @csrf
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 mt-2 sm:mt-0">チームを離脱</button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 relative">
                    <div id="calendar" data-events="{{ json_encode($events) }}"></div>
                    <div id="calendar-overlay" class="hidden"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="eventModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 hidden z-50 p-4">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg">
            <h2 class="text-xl font-bold mb-4">イベントを作成</h2>
            <form id="eventForm" method="POST" action="{{ route('events.store') }}">
                @csrf
                <input type="hidden" name="team_id" value="{{ $team->id }}">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">イベント名</label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" list="event-names">
                </div>
                <div class="mb-4">
                    <label for="start_datetime" class="block text-sm font-medium text-gray-700">開始日時</label>
                    <input type="datetime-local" name="start_datetime" id="start_datetime" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="mb-4">
                    <label for="end_datetime" class="block text-sm font-medium text-gray-700">終了日時</label>
                    <input type="datetime-local" name="end_datetime" id="end_datetime" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="mb-4">
                    <label for="memo" class="block text-sm font-medium text-gray-700">メモ</label>
                    <textarea name="memo" id="memo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal()" class="mr-2 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">キャンセル</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">保存</button>
                </div>
            </form>
        </div>
    </div>

    <div id="eventDetailModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 hidden z-50 p-4">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg">
            <h2 class="text-xl font-bold mb-4">イベントの詳細</h2>
            <p class="text-lg mb-2"><strong>イベント名:</strong> <span id="eventDetailName"></span></p>
            <p class="text-lg mb-2"><strong>開始日時:</strong> <span id="eventDetailStart"></span></p>
            <p class="text-lg mb-2"><strong>終了日時:</strong> <span id="eventDetailEnd"></span></p>
            <p class="text-lg mb-2"><strong>作成者:</strong> <span id="eventDetailCreatedBy"></span></p> <!-- 位置変更 -->
            <p class="text-lg mb-2"><strong>メモ:</strong> <span id="eventDetailMemo"></span></p>
            <h3 class="text-lg font-bold mt-4 mb-2">コメント</h3>
            <div id="eventDetailComments" class="mb-4 space-y-2"></div>
            <form id="commentForm" method="POST" action="{{ route('comments.store') }}">
                @csrf
                <input type="hidden" name="event_id" id="commentEventId">
                <div class="mb-4">
                    <label for="comment" class="block text-sm font-medium text-gray-700">コメントを追加</label>
                    <textarea name="content" id="comment" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">送信</button>
                </div>
            </form>
            <div class="flex justify-end mt-4">
                <button type="button" onclick="closeEventModal()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">閉じる</button>
            </div>
        </div>
    </div>

    <div id="dayModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 hidden z-50 p-4">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg">
            <h2 class="text-xl font-bold mb-4">予定一覧 <span id="dayModalDate"></span></h2>
            <div id="dayEventsContainer" class="mb-4"></div>
            <div class="flex justify-end">
                <button type="button" onclick="closeDayModal()" class="mr-2 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">閉じる</button>
                <button type="button" id="openAddModalButton" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">追加</button>
            </div>
        </div>
    </div>

    <datalist id="event-names"></datalist>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/calendar.js'])
</x-app-layout>