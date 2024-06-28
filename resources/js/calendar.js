import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import jaLocale from '@fullcalendar/core/locales/ja';  // 日本語ロケールのインポート

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var events = JSON.parse(calendarEl.dataset.events);
    var currentDay = null; // 追加：現在開いている日付を保持

    // コンソール出力でデータを確認
    console.log('Events:', events);

    var calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locale: jaLocale,  // ロケールを日本語に設定
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek,dayGridDay'
        },
        buttonText: {
            today: '今日に戻る'
        },
        events: events, // イベントデータを設定
        dateClick: function(info) {
            openDayModal(info.dateStr);
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault(); // Prevent navigating to event details from calendar
        },
        eventContent: function(arg) {
            let dot = `<div class="dot" style="background-color:${arg.event.extendedProps.color}; display: inline-block; margin-right: 5px;"></div>`;
            let title = arg.event.title;
            if (arg.event.extendedProps.comments && arg.event.extendedProps.comments.length > 0) {
                title = `<span class="red-alert">⚠️</span> ${title}`;
            }
            return { html: dot + `<span class="fc-event-title" style="pointer-events: none;">${title}</span>` };
        },
        dayCellDidMount: function(info) {
            info.el.classList.add('hover:bg-gray-200', 'cursor-pointer'); // Add hover and cursor classes to the day cells
        }
    });
    calendar.render();

    var nameInput = document.getElementById('name');
    nameInput.addEventListener('input', function() {
        fetch('/events/names?q=' + nameInput.value)
            .then(response => response.json())
            .then(data => {
                var datalist = document.getElementById('event-names');
                datalist.innerHTML = '';
                data.forEach(function(item) {
                    var option = document.createElement('option');
                    option.value = item;
                    datalist.appendChild(option);
                });
            });
    });

    function openDayModal(dateStr) {
        currentDay = dateStr; // 追加：現在の日付を設定
        const dayEvents = events.filter(event => event.start.startsWith(dateStr));
        const dayEventsContainer = document.getElementById('dayEventsContainer');
        dayEventsContainer.innerHTML = '';

        console.log('Day Events:', dayEvents); // 追加：デバッグ用ログ

        dayEvents.forEach(event => {
            const user = event.extendedProps.user || 'Unknown';
            const start = new Date(event.start);
            const end = event.end ? new Date(event.end) : null;
            const startHours = String(start.getHours()).padStart(2, '0');
            const startMinutes = String(start.getMinutes()).padStart(2, '0');
            const endHours = end ? String(end.getHours()).padStart(2, '0') : '';
            const endMinutes = end ? String(end.getMinutes()).padStart(2, '0') : '';
            const timeRange = end ? `${startHours}:${startMinutes} - ${endHours}:${endMinutes}` : `${startHours}:${startMinutes}`;

            const dot = `<div class="dot" style="background-color:${event.extendedProps.color}; display: inline-block; margin-right: 5px;"></div>`;
            const alert = event.extendedProps.comments && event.extendedProps.comments.length > 0 ? `<span class="red-alert">⚠️</span> ` : '';

            const eventElement = document.createElement('div');
            eventElement.classList.add('day-event', 'bg-gray-100', 'p-2', 'mb-2', 'rounded', 'cursor-pointer', 'hover:bg-gray-200');
            eventElement.innerHTML = `${dot}<strong>${alert}${event.title}</strong> (${timeRange}, ${user})`;
            eventElement.addEventListener('click', () => openEventModal(event));
            dayEventsContainer.appendChild(eventElement);
        });

        document.getElementById('dayModalDate').innerText = new Date(dateStr).toLocaleDateString();
        document.getElementById('dayModal').classList.remove('hidden');
        document.getElementById('calendar-overlay').classList.remove('hidden'); // Show overlay to prevent calendar clicks
    }

    function openModal(dateStr = null) {
        const now = dateStr ? new Date(dateStr) : new Date();
        const formattedDate = now.toISOString().slice(0, 16);

        document.getElementById('start_datetime').value = formattedDate;
        document.getElementById('end_datetime').value = formattedDate;
        document.getElementById('eventModal').classList.remove('hidden');
        document.getElementById('name').focus();
    }

    document.getElementById('openAddModalButton').addEventListener('click', function() {
        openModal(currentDay); // 追加：現在開いている日付を渡す
    });

    function openEventModal(event) {
        console.log('Event:', event); // イベントデータをコンソールに出力

        // 開始日時と終了日時をローカルタイムゾーンで表示するために調整
        const start = new Date(event.start);
        const end = event.end ? new Date(event.end) : null;

        document.getElementById('eventDetailName').innerText = event.title;
        document.getElementById('eventDetailStart').innerText = start.toLocaleString();
        document.getElementById('eventDetailEnd').innerText = end ? end.toLocaleString() : 'なし';
        document.getElementById('eventDetailMemo').innerText = event.extendedProps.memo || 'No memo';

        // 作成者情報を追加
        document.getElementById('eventDetailCreatedBy').innerHTML = `<div class="dot" style="background-color:${event.extendedProps.created_by_color};"></div>${event.extendedProps.created_by}`;

        // コメントを表示
        document.getElementById('eventDetailComments').innerHTML = '';
        if (event.extendedProps.comments && event.extendedProps.comments.length > 0) {
            event.extendedProps.comments.forEach(comment => {
                const commentEl = document.createElement('div');
                commentEl.innerHTML = `<div class="dot" style="background-color:${comment.user_color};"></div><strong>${comment.user}:</strong> ${comment.content}`;
                document.getElementById('eventDetailComments').appendChild(commentEl);
            });
        }

        document.getElementById('commentEventId').value = event.id;
        document.getElementById('eventDetailModal').classList.remove('hidden');
    }

    function closeDayModal() {
        document.getElementById('dayModal').classList.add('hidden');
        document.getElementById('calendar-overlay').classList.add('hidden'); // Hide overlay to allow calendar clicks
    }

    function closeModal() {
        document.getElementById('eventModal').classList.add('hidden');
    }

    function closeEventModal() {
        document.getElementById('eventDetailModal').classList.add('hidden');
    }

    window.openModal = openModal;
    window.closeDayModal = closeDayModal;
    window.closeModal = closeModal;
    window.closeEventModal = closeEventModal;
});