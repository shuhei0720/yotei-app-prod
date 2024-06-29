import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import jaLocale from '@fullcalendar/core/locales/ja';
import { between } from 'holiday-jp';
import { format, parseISO } from 'date-fns';

document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM Content Loaded");

    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error("Calendar element not found");
        return;
    }
    console.log("Calendar element found:", calendarEl);

    var events = JSON.parse(calendarEl.dataset.events || '[]');
    var currentDay = null;

    function addJapaneseHolidaysToEvents(events) {
        console.log("Adding Japanese holidays to events");
        const year = new Date().getFullYear();
        const holidays = between(new Date(year, 0, 1), new Date(year, 11, 31));
        holidays.forEach(holiday => {
            const holidayDate = holiday.date;
            console.log("Original holiday date:", holidayDate);

            const formattedDate = format(holidayDate, 'yyyy-MM-dd');
            console.log("Formatted holiday date:", formattedDate);

            events.push({
                title: holiday.name,
                start: formattedDate,
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
        events: events.map(event => ({
            ...event,
            allDay: event.allDay // 全てのイベントにallDayプロパティを追加
        })),
        dateClick: function(info) {
            console.log("Date clicked:", info.dateStr);
            openDayModal(info.dateStr);
        },
        eventClick: function(info) {
            console.log("Event clicked:", info.event);
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
                arg.dayNumberText = '';

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
            } else {
                return { domNodes: [] };
            }
        }
    });
    calendar.render();
    console.log("Calendar rendered");

    var nameInput = document.getElementById('name');
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            console.log("Name input changed:", nameInput.value);
            fetch('/events/names?q=' + nameInput.value)
                .then(response => response.json())
                .then(data => {
                    var datalist = document.getElementById('event-names');
                    if (datalist) {
                        datalist.innerHTML = '';
                        data.forEach(function(item) {
                            var option = document.createElement('option');
                            option.value = item;
                            datalist.appendChild(option);
                        });
                    }
                });
        });
    } else {
        console.error("Name input element not found");
    }

    function openDayModal(dateStr) {
        currentDay = dateStr;
        const dayEvents = events.filter(event => event.start.startsWith(dateStr));
        const dayEventsContainer = document.getElementById('dayEventsContainer');
        if (dayEventsContainer) {
            dayEventsContainer.innerHTML = '';

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
        } else {
            console.error("Day events container not found");
        }
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
        const start = new Date(event.start);
        const end = event.end ? new Date(event.end) : null;

        document.getElementById('eventDetailName').innerText = event.title;
        document.getElementById('eventDetailStart').innerText = start.toLocaleString();
        document.getElementById('eventDetailEnd').innerText = end ? end.toLocaleString() : 'なし';
        document.getElementById('eventDetailMemo').innerText = event.extendedProps.memo || 'No memo';

        const createdBy = event.extendedProps.created_by || 'なし';
        const createdByColor = event.extendedProps.created_by_color || 'transparent';

        document.getElementById('eventDetailCreatedBy').innerHTML = `<div class="dot" style="background-color:${createdByColor};"></div>${createdBy}`;

        const eventDetailComments = document.getElementById('eventDetailComments');
        if (eventDetailComments) {
            eventDetailComments.innerHTML = '';
            if (event.extendedProps.comments && event.extendedProps.comments.length > 0) {
                event.extendedProps.comments.forEach(comment => {
                    const commentEl = document.createElement('div');
                    commentEl.innerHTML = `<div class="dot" style="background-color:${comment.user_color};"></div><strong>${comment.user}:</strong> ${comment.content}`;
                    eventDetailComments.appendChild(commentEl);
                });
            }
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

    // 終日イベントの処理
    document.getElementById('all_day').addEventListener('change', function() {
        const startInput = document.getElementById('start_datetime');
        const endInput = document.getElementById('end_datetime');

        console.log("All Day checkbox changed:", this.checked);

        if (this.checked) {
            const date = new Date(startInput.value);
            const startDate = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate(), 0, 0));
            const endDate = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate(), 23, 59));

            console.log("Start Date (All Day):", startDate);
            console.log("End Date (All Day):", endDate);

            startInput.value = startDate.toISOString().slice(0, 16);
            endInput.value = endDate.toISOString().slice(0, 16);
            startInput.disabled = true;
            endInput.disabled = true;
        } else {
            console.log("All Day Unchecked");
            startInput.disabled = false;
            endInput.disabled = false;
        }
    });

    window.openModal = openModal;
    window.closeDayModal = closeDayModal;
    window.closeModal = closeModal;
    window.closeEventModal = closeEventModal;
});