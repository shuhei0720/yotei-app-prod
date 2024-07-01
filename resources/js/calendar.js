import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import jaLocale from '@fullcalendar/core/locales/ja';
import { between } from 'holiday-jp';
import { format } from 'date-fns';
import moment from 'moment-timezone';

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        return;
    }

    var events = JSON.parse(calendarEl.dataset.events || '[]');
    var currentDay = null;

    function addJapaneseHolidaysToEvents(events) {
        const year = new Date().getFullYear();
        const holidays = between(new Date(year, 0, 1), new Date(year, 11, 31));
        holidays.forEach(holiday => {
            const formattedDate = format(holiday.date, 'yyyy-MM-dd');
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
        timeZone: 'Asia/Tokyo',
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
            allDay: event.allDay
        })),
        eventDisplay: 'block',
        dayMaxEvents: true,
        dateClick: function(info) {
            openDayModal(info.dateStr);
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            openEventModal(info.event);
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
        },
        slotEventOverlap: false,
        allDaySlot: false,
        slotLabelFormat: { hour: 'numeric', minute: '2-digit', hour12: false },
        scrollTime: '06:00:00',
        nowIndicator: true,
        selectable: true,
        select: function(info) {
            openDayModal(info.startStr);
        }
    });
    calendar.render();

    function adjustDayCellHeights() {
        if (window.innerWidth <= 768) {
            const dayCells = document.querySelectorAll('.fc-daygrid-day');
            dayCells.forEach(cell => {
                const events = cell.querySelectorAll('.fc-daygrid-event');
                if (events.length > 4) {
                    cell.style.height = `${events.length * 20}px`;
                } else {
                    cell.style.height = 'auto';
                }
            });
        }
    }

    adjustDayCellHeights();
    window.addEventListener('resize', adjustDayCellHeights);
    calendar.on('eventsSet', adjustDayCellHeights);

    var nameInput = document.getElementById('name');
    if (nameInput) {
        nameInput.addEventListener('input', function() {
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
    }

    function openDayModal(dateStr) {
        currentDay = dateStr.split('T')[0];
        const dayEvents = events.filter(event => event.start.startsWith(currentDay));
        const dayEventsContainer = document.getElementById('dayEventsContainer');
        if (dayEventsContainer) {
            dayEventsContainer.innerHTML = '';

            dayEvents.forEach(event => {
                const extendedProps = event.extendedProps || {};
                const user = extendedProps.user || 'なし';
                const start = moment.tz(event.start, 'Asia/Tokyo').toDate();
                const end = event.end ? moment.tz(event.end, 'Asia/Tokyo').toDate() : null;
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

            document.getElementById('dayModalDate').innerText = moment.tz(currentDay, 'Asia/Tokyo').format('YYYY/MM/DD');
            document.getElementById('dayModal').classList.remove('hidden');
            document.getElementById('calendar-overlay').classList.remove('hidden');
        }
    }

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

    document.getElementById('openAddModalButton').addEventListener('click', function() {
        openModal(currentDay);
    });

    function openEventModal(event) {
        const start = moment.tz(event.start, 'Asia/Tokyo').toDate();
        const end = event.end ? moment.tz(event.end, 'Asia/Tokyo').toDate() : null;

        document.getElementById('eventDetailName').innerText = event.title;
        document.getElementById('eventDetailStart').innerText = moment(start).format('YYYY/MM/DD HH:mm');
        document.getElementById('eventDetailStart').setAttribute('data-datetime', moment(start).format('YYYY-MM-DDTHH:mm'));
        document.getElementById('eventDetailEnd').innerText = end ? moment(end).format('YYYY/MM/DD HH:mm') : 'なし';
        if (end) {
            document.getElementById('eventDetailEnd').setAttribute('data-datetime', moment(end).format('YYYY-MM-DDTHH:mm'));
        }
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

    function closeEditModal() {
        document.getElementById('editEventModal').classList.add('hidden');
    }

    function closeEventModal() {
        document.getElementById('eventDetailModal').classList.add('hidden');
    }

    document.getElementById('all_day').addEventListener('change', function() {
        const startInput = document.getElementById('start_datetime');
        const endInput = document.getElementById('end_datetime');

        if (this.checked) {
            const date = new Date(startInput.value);
            const startDate = moment.tz(date, 'Asia/Tokyo').startOf('day').toDate();
            const endDate = moment.tz(date, 'Asia/Tokyo').endOf('day').toDate();

            startInput.value = moment(startDate).format('YYYY-MM-DDTHH:mm');
            endInput.value = moment(endDate).format('YYYY-MM-DDTHH:mm');
            startInput.disabled = true;
            endInput.disabled = true;
        } else {
            startInput.disabled = false;
            endInput.disabled = false;
        }
    });

    document.getElementById('edit_all_day').addEventListener('change', function() {
        const startInput = document.getElementById('edit_start_datetime');
        const endInput = document.getElementById('edit_end_datetime');

        if (this.checked) {
            const date = new Date(startInput.value);
            const startDate = moment.tz(date, 'Asia/Tokyo').startOf('day').toDate();
            const endDate = moment.tz(date, 'Asia/Tokyo').endOf('day').toDate();

            startInput.value = moment(startDate).format('YYYY-MM-DDTHH:mm');
            endInput.value = moment(endDate).format('YYYY-MM-DDTHH:mm');
            startInput.disabled = true;
            endInput.disabled = true;
        } else {
            startInput.disabled = false;
            endInput.disabled = false;
        }
    });

    window.openModal = openModal;
    window.closeDayModal = closeDayModal;
    window.closeModal = closeModal;
    window.closeEventModal = closeEventModal;
});