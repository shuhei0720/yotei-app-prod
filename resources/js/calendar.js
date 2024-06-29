import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import jaLocale from '@fullcalendar/core/locales/ja';
import { between } from 'holiday-jp';

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var events = JSON.parse(calendarEl.dataset.events);
    var currentDay = null;

    console.log('Events:', events);

    function addJapaneseHolidaysToEvents(events) {
        const year = new Date().getFullYear();
        const holidays = between(new Date(year, 0, 1), new Date(year, 11, 31));
        holidays.forEach(holiday => {
            events.push({
                title: holiday.name,
                start: holiday.date.toISOString().split('T')[0],
                color: 'red',
                allDay: true,
                extendedProps: {
                    created_by: 'なし',
                    created_by_color: 'transparent'
                }
            });
        });
        return events;
    }

    events = addJapaneseHolidaysToEvents(events);

    var calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locale: jaLocale,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: '今日に戻る'
        },
        events: events,
        dateClick: function(info) {
            openDayModal(info.dateStr);
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
        },
        eventContent: function(arg) {
            let dot = `<div class="dot" style="background-color:${arg.event.extendedProps.color}; display: inline-block; margin-right: 5px;"></div>`;
            let title = arg.event.title;
            if (arg.event.extendedProps.comments && arg.event.extendedProps.comments.length > 0) {
                title = `<span class="red-alert">⚠️</span> ${title}`;
            }
            return { html: dot + `<span class="fc-event-title" style="pointer-events: none;">${title}</span>` };
        },
        dayCellContent: function(arg) {
            if (calendar.view.type === 'dayGridMonth') {
                // デフォルトの日付表示を削除
                arg.dayNumberText = '';

                // カスタム日付表示を追加
                const date = new Date(arg.date);
                const dayNumber = date.getDate();
                const dayEl = document.createElement('a');
                dayEl.classList.add('fc-daygrid-day-number');
                dayEl.innerText = dayNumber;
                if (date.getDay() === 0) {
                    dayEl.style.color = 'red';
                } else if (date.getDay() === 6) {
                    dayEl.style.color = 'blue';
                }

                return { domNodes: [dayEl] };
            }
            // 週表示と日表示では日付を非表示にする
            return { domNodes: [] };
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
        currentDay = dateStr;
        const dayEvents = events.filter(event => event.start.startsWith(dateStr));
        const dayEventsContainer = document.getElementById('dayEventsContainer');
        dayEventsContainer.innerHTML = '';

        console.log('Day Events:', dayEvents);

        dayEvents.forEach(event => {
            const extendedProps = event.extendedProps || {};
            const user = extendedProps.user || 'なし';
            const start = new Date(event.start);
            const end = event.end ? new Date(event.end) : null;
            const startHours = String(start.getHours()).padStart(2, '0');
            const startMinutes = String(start.getMinutes()).padStart(2, '0');
            const endHours = end ? String(end.getHours()).padStart(2, '0') : '';
            const endMinutes = end ? String(end.getMinutes()).padStart(2, '0') : '';
            const timeRange = end ? `${startHours}:${startMinutes} - ${endHours}:${endMinutes}` : `${startHours}:${startMinutes}`;

            const dot = `<div class="dot" style="background-color:${extendedProps.color || 'transparent'}; display: inline-block; margin-right: 5px;"></div>`;
            const alert = extendedProps.comments && extendedProps.comments.length > 0 ? `<span class="red-alert">⚠️</span> ` : '';

            const eventElement = document.createElement('div');
            eventElement.classList.add('day-event', 'bg-gray-100', 'p-2', 'mb-2', 'rounded', 'cursor-pointer', 'hover:bg-gray-200');
            eventElement.innerHTML = `${dot}<strong>${alert}${event.title}</strong> (${timeRange}, ${user})`;
            eventElement.addEventListener('click', () => openEventModal(event));
            dayEventsContainer.appendChild(eventElement);
        });

        document.getElementById('dayModalDate').innerText = new Date(dateStr).toLocaleDateString();
        document.getElementById('dayModal').classList.remove('hidden');
        document.getElementById('calendar-overlay').classList.remove('hidden');
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
        openModal(currentDay);
    });

    function openEventModal(event) {
        console.log('Event:', event);

        const start = new Date(event.start);
        const end = event.end ? new Date(event.end) : null;

        document.getElementById('eventDetailName').innerText = event.title;
        document.getElementById('eventDetailStart').innerText = start.toLocaleString();
        document.getElementById('eventDetailEnd').innerText = end ? end.toLocaleString() : 'なし';
        document.getElementById('eventDetailMemo').innerText = event.extendedProps.memo || 'No memo';

        const createdBy = event.extendedProps.created_by || 'なし';
        const createdByColor = event.extendedProps.created_by_color || 'transparent';

        document.getElementById('eventDetailCreatedBy').innerHTML = `<div class="dot" style="background-color:${createdByColor};"></div>${createdBy}`;

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
        document.getElementById('calendar-overlay').classList.add('hidden');
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