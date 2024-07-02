<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-center items-center flex-wrap header">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight text-center flex items-center space-x-2">
                <span class="bg-gray-300 text-gray-700 px-2 py-1 rounded text-sm font-semibold">チームID: {{ $team->team_id }}</span>
                <span class="text-lg font-bold text-blue-600">{{ $team->name }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-0" style="padding:1px 0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 bg-white border-b border-gray-200 relative">
                    <div id="calendar" class="calendar-container" data-events="{{ json_encode($events) }}"></div>
                    <div id="calendar-overlay" class="hidden"></div>
                    <input type="hidden" id="team_id" value="{{ $team->id }}">
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
                    <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" list="event-names" required>
                </div>
                <div class="mb-4">
                    <label for="start_datetime" class="block text-sm font-medium text-gray-700">開始日時</label>
                    <input type="datetime-local" name="start_datetime" id="start_datetime" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="mb-4">
                    <label for="end_datetime" class="block text-sm font-medium text-gray-700">終了日時</label>
                    <input type="datetime-local" name="end_datetime" id="end_datetime" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="mb-4">
                    <input type="checkbox" name="all_day" id="all_day">
                    <label for="all_day" class="text-sm font-medium text-gray-700">終日</label>
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
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg relative">
            <div class="absolute top-10 right-4 flex space-x-2">
                <button type="button" onclick="openEditModal()" class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600">編集</button>
                <button type="button" onclick="confirmDeleteEvent()" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">削除</button>
            </div>
            <h2 class="text-xl font-bold mb-4">イベントの詳細</h2>
            <p class="text-lg mb-2"><strong>イベント名:</strong> <span id="eventDetailName"></span></p>
            <p class="text-lg mb-2"><strong>開始日時:</strong> <span id="eventDetailStart" data-datetime=""></span></p>
            <p class="text-lg mb-2"><strong>終了日時:</strong> <span id="eventDetailEnd" data-datetime=""></span></p>
            <p class="text-lg mb-2"><strong>作成者:</strong> <span id="eventDetailCreatedBy"></span></p>
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

    <div id="editEventModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 hidden z-50 p-4">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg">
            <h2 class="text-xl font-bold mb-4">イベントを編集</h2>
            <form id="editEventForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="team_id" value="{{ $team->id }}">
                <input type="hidden" name="event_id" id="edit_event_id">
                <div class="mb-4">
                    <label for="edit_name" class="block text-sm font-medium text-gray-700">イベント名</label>
                    <input type="text" name="name" id="edit_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="mb-4">
                    <label for="edit_start_datetime" class="block text-sm font-medium text-gray-700">開始日時</label>
                    <input type="datetime-local" name="start_datetime" id="edit_start_datetime" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="mb-4">
                    <label for="edit_end_datetime" class="block text-sm font-medium text-gray-700">終了日時</label>
                    <input type="datetime-local" name="end_datetime" id="edit_end_datetime" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="mb-4">
                    <input type="checkbox" name="all_day" id="edit_all_day">
                    <label for="edit_all_day" class="text-sm font-medium text-gray-700">終日</label>
                </div>
                <div class="mb-4">
                    <label for="edit_memo" class="block text-sm font-medium text-gray-700">メモ</label>
                    <textarea name="memo" id="edit_memo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditModal()" class="mr-2 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">キャンセル</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">保存</button>
                </div>
            </form>
        </div>
    </div>

    <div id="dayModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 hidden z-50 p-4">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg">
            <div class="modal-header dayModal-header">
                <h2 class="modal-title">予定一覧 <span id="dayModalDate"></span></h2>
            </div>
            <div class="modal-body">
                <div id="dayEventsContainer"></div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeDayModal()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">閉じる</button>
                <button type="button" id="openAddModalButton" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">追加</button>
            </div>
        </div>
    </div>

    <div id="longPressModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 hidden z-50 p-4">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg">
            <div class="modal-header longPressModal-header">
                <h2 class="modal-title">過去の予定から登録 <span id="longPressModalDate"></span></h2>
            </div>
            <div class="modal-body">
                <div id="longPressModalContainer"></div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeLongPressModal()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">閉じる</button>
            </div>
        </div>
    </div>

    <datalist id="event-names"></datalist>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/calendar.js'])
</x-app-layout>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js"></script>

<script>
    document.getElementById('eventForm').addEventListener('submit', function(event) {
        event.preventDefault(); // デフォルトのフォーム送信を防止
        const formData = new FormData(event.target);
        const allDay = document.getElementById('all_day').checked;

        if (allDay) {
            const startInput = document.getElementById('start_datetime');
            const endInput = document.getElementById('end_datetime');
            const date = new Date(startInput.value);
            const startDate = moment.tz(date, 'Asia/Tokyo').startOf('day').toDate();
            const endDate = moment.tz(date, 'Asia/Tokyo').endOf('day').toDate();
            formData.set('start_datetime', moment(startDate).format('YYYY-MM-DDTHH:mm'));
            formData.set('end_datetime', moment(endDate).format('YYYY-MM-DDTHH:mm'));
            formData.set('all_day', 'true');
        } else {
            formData.set('all_day', 'false');
        }

        fetch("{{ route('events.store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': formData.get('_token')
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'エラーが発生しました。');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                closeModal();
                window.location.reload(); // ページをリロードしてカレンダーを更新
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            alert('エラー: ' + error.message);
        });
    });

    document.getElementById('editEventForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const allDay = document.getElementById('edit_all_day').checked;

        if (allDay) {
            const startInput = document.getElementById('edit_start_datetime');
            const endInput = document.getElementById('edit_end_datetime');
            const date = new Date(startInput.value);
            const startDate = moment.tz(date, 'Asia/Tokyo').startOf('day').toDate();
            const endDate = moment.tz(date, 'Asia/Tokyo').endOf('day').toDate();
            formData.set('start_datetime', moment(startDate).format('YYYY-MM-DDTHH:mm'));
            formData.set('end_datetime', moment(endDate).format('YYYY-MM-DDTHH:mm'));
            formData.set('all_day', 'true');
        } else {
            formData.set('all_day', 'false');
        }

        const eventId = formData.get('event_id');
        fetch("{{ route('events.update', '') }}/" + eventId, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': formData.get('_token'),
                'X-HTTP-Method-Override': 'PUT'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'エラーが発生しました。');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                closeEditModal();
                window.location.reload(); // ページをリロードしてカレンダーを更新
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            alert('エラー: ' + error.message);
        });
    });

    function openModal(dateStr = null) {
        console.log("openModal called with dateStr:", dateStr);
        const now = dateStr ? moment.tz(dateStr, 'Asia/Tokyo').toDate() : moment.tz('Asia/Tokyo').toDate();
        console.log("current datetime in Asia/Tokyo:", now);
        const formattedDate = moment(now).format('YYYY-MM-DDTHH:mm');
        console.log("formatted datetime:", formattedDate);

        document.getElementById('start_datetime').value = formattedDate;
        document.getElementById('end_datetime').value = formattedDate;
        document.getElementById('eventModal').classList.remove('hidden');
        document.getElementById('name').focus();
    }

    function closeModal() {
        document.getElementById('eventModal').classList.add('hidden');
    }

    function closeEditModal() {
        document.getElementById('editEventModal').classList.add('hidden');
    }

    function openEditModal() {
        document.getElementById('editEventModal').classList.remove('hidden');
        // イベントの詳細をフォームに反映する
        const eventDetail = {
            name: document.getElementById('eventDetailName').innerText,
            start_datetime: document.getElementById('eventDetailStart').getAttribute('data-datetime'),
            end_datetime: document.getElementById('eventDetailEnd').getAttribute('data-datetime'),
            memo: document.getElementById('eventDetailMemo').innerText,
        };

        document.getElementById('edit_name').value = eventDetail.name;
        document.getElementById('edit_start_datetime').value = eventDetail.start_datetime;
        document.getElementById('edit_end_datetime').value = eventDetail.end_datetime;
        document.getElementById('edit_memo').value = eventDetail.memo;

        // 全日かどうかのチェックを反映
        const allDayCheckbox = document.getElementById('edit_all_day');
        if (eventDetail.start_datetime.includes('T00:00') && eventDetail.end_datetime.includes('T23:59')) {
            allDayCheckbox.checked = true;
        } else {
            allDayCheckbox.checked = false;
        }

        // 編集するイベントIDを設定
        document.getElementById('edit_event_id').value = document.getElementById('commentEventId').value;
    }

    function confirmDeleteEvent() {
        if (confirm("予定を削除しますか？")) {
            deleteEvent();
        }
    }

    function deleteEvent() {
        const eventId = document.getElementById('commentEventId').value;
        fetch("{{ route('events.destroy', '') }}/" + eventId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-HTTP-Method-Override': 'DELETE'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'エラーが発生しました。');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                closeEventModal();
                window.location.reload(); // ページをリロードしてカレンダーを更新
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            alert('エラー: ' + error.message);
        });
    }

    function closeEventModal() {
        document.getElementById('eventDetailModal').classList.add('hidden');
    }

    function closeLongPressModal() {
        document.getElementById('longPressModal').classList.add('hidden');
        document.getElementById('calendar-overlay').classList.add('hidden');
    }
</script>

<style>
    #editEventModal {
        z-index: 10000; /* 最前面に表示 */
    }
    .modal-header {
        font-size: 1.5rem;
        font-weight: bold;
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center; /* Align items vertically */
        justify-content: space-between; /* Add space between title and date */
    }

    .dayModal-header {
        background-color: #f0f8ff;
        color: #000080;
    }

    .longPressModal-header {
        background-color: #ffe4e1;
        color: #b22222;
    }

    .modal-body {
        padding: 1rem;
    }

    .modal-footer {
        padding: 0.5rem 1rem;
        text-align: right;
    }

    .modal-footer button {
        margin-left: 0.5rem;
    }

    .day-event, .past-event {
        margin-bottom: 0.5rem; /* Ensure some space between events */
        padding: 0.75rem; /* Adjust padding for better touch target */
        line-height: 1.3; /* Adjust line height for better readability */
        background-color: #f9f9f9; /* Add background color for better visibility */
        border-radius: 0.25rem; /* Round the corners for a modern look */
        border: 1px solid #ddd; /* Add border for separation */
        cursor: pointer; /* Change cursor to pointer for better UX */
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: bold;
        margin: 0; /* Remove bottom margin */
    }

    .modal-title span {
        font-size: 1rem;
        font-weight: normal;
        color: #555;
        margin-left: 1rem;
    }

    #eventDetailModal .absolute {
        top: 2.5rem;
        right: 1rem;
    }
</style>