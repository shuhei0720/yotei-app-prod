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
    var longPressTimeout;
    var longPressTriggered = false;
    var longPressModalOpen = false;
    var clickTimeThreshold = 500;
    var touchMoveDetected = false;
    var isTouch = false;

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
        dayMaxEvents: 10,
        dateClick: function(info) {
            if (!longPressTriggered && !longPressModalOpen && !isTouch) {
                console.log("Date clicked:", info.dateStr);
                openDayModal(info.dateStr);
            } else {
                console.log("Long press triggered or modal open, date click ignored:", info.dateStr);
            }
        },
        eventClick: function(info) {
            if (!longPressModalOpen && !isTouch) {
                info.jsEvent.preventDefault();
                console.log("Event clicked:", info.event.title);
                openEventModal(info.event);
            } else {
                console.log("Modal open or touch event, event click ignored:", info.event.title);
            }
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
            if (!longPressModalOpen && !isTouch) {
                openDayModal(info.startStr);
            }
        }
    });
    calendar.render();

    function adjustDayCellHeights() {
        if (window.innerWidth <= 768) {
            const dayCells = document.querySelectorAll('.fc-daygrid-day, .fc-timegrid-slot');
            dayCells.forEach(cell => {
                const events = cell.querySelectorAll('.fc-daygrid-event, .fc-timegrid-event');
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
        console.log("openDayModal called with dateStr:", dateStr);
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

    function openLongPressModal(dateStr) {
        console.log("openLongPressModal called with dateStr:", dateStr);
        currentDay = dateStr.split('T')[0];
        fetch('/events/user', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'error') {
                throw new Error(data.message);
            }
            const pastEvents = data.events.slice(0, 5);
            const longPressModalContainer = document.getElementById('longPressModalContainer');
            if (longPressModalContainer) {
                longPressModalContainer.innerHTML = '';

                pastEvents.forEach(event => {
                    const start = moment.tz(event.start_datetime, 'Asia/Tokyo').toDate();
                    const end = event.end_datetime ? moment.tz(event.end_datetime, 'Asia/Tokyo').toDate() : null;
                    const startTime = moment(start).format('HH:mm');
                    const endTime = end ? moment(end).format('HH:mm') : '';
                    const timeRange = end ? `${startTime} - ${endTime}` : startTime;
                    const allDay = event.all_day ? '終日' : timeRange;

                    const eventElement = document.createElement('div');
                    eventElement.classList.add('day-event', 'bg-gray-100', 'p-2', 'mb-2', 'rounded', 'cursor-pointer', 'hover:bg-gray-200');
                    eventElement.innerHTML = `<strong>${event.name}</strong> (${allDay})`;
                    eventElement.addEventListener('click', () => addEventToDate(event));
                    longPressModalContainer.appendChild(eventElement);
                });

                document.getElementById('longPressModalDate').innerText = moment.tz(currentDay, 'Asia/Tokyo').format('YYYY/MM/DD');
                document.getElementById('longPressModal').classList.remove('hidden');
                document.getElementById('calendar-overlay').classList.remove('hidden');
                longPressModalOpen = true;
            }
        })
        .catch(error => {
            console.error('Error fetching past events:', error);
        });
    }

    function addEventToDate(event) {
        console.log("Adding event to date:", currentDay);
        const teamId = document.getElementById('team_id')?.value;

        if (!teamId) {
            console.error('Team ID is not found.');
            return;
        }

        let startDatetime = event.start_datetime ? currentDay + 'T' + moment(event.start_datetime).format('HH:mm') : null;
        let endDatetime = event.end_datetime ? currentDay + 'T' + moment(event.end_datetime).format('HH:mm') : null;

        if (event.all_day) {
            startDatetime = currentDay + 'T00:00';
            endDatetime = currentDay + 'T23:59';
        }

        console.log("New event start datetime:", startDatetime);
        console.log("New event end datetime:", endDatetime);

        if (!startDatetime || !endDatetime) {
            console.error('Invalid start or end datetime.');
            return;
        }

        const newEvent = {
            team_id: teamId,
            name: event.name,
            start_datetime: startDatetime,
            end_datetime: endDatetime,
            all_day: event.all_day,
            memo: ''
        };

        fetch('/events', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(newEvent)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                closeLongPressModal();
                window.location.reload();
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            console.error('Error adding event:', error);
        });
    }

    function handleMouseDown(e) {
        if (isTouch) return;
        console.log("Mouse down detected");
        longPressTriggered = false;
        touchMoveDetected = false;
        const targetDate = e.target.closest('.fc-daygrid-day, .fc-timegrid-slot')?.getAttribute('data-date');
        longPressTimeout = setTimeout(function() {
            if (!touchMoveDetected) {
                longPressTriggered = true;
                console.log("Long press detected");
                if (targetDate) {
                    openLongPressModal(targetDate);
                }
            }
        }, clickTimeThreshold);
    }

    function handleMouseUp(e) {
        if (isTouch) return;
        console.log("Mouse up detected");
        clearTimeout(longPressTimeout);
        if (!longPressTriggered && !longPressModalOpen) {
            console.log("Short press detected");
            const clickedDate = e.target.closest('.fc-daygrid-day, .fc-timegrid-slot')?.getAttribute('data-date');
            if (clickedDate) {
                openDayModal(clickedDate);
            }
        } else if (longPressTriggered) {
            console.log("Long press ended");
        }
        longPressTriggered = false;
    }

    function addEventListenersToCells() {
        document.querySelectorAll('.fc-daygrid-day, .fc-timegrid-slot').forEach(dayCell => {
            dayCell.addEventListener('mousedown', handleMouseDown);
            dayCell.addEventListener('mouseup', handleMouseUp);
            dayCell.addEventListener('mouseleave', function(e) {
                if (isTouch) return;
                clearTimeout(longPressTimeout);
                console.log("Mouse left calendar area, long press canceled");
            });
            dayCell.addEventListener('touchstart', handleTouchStart);
            dayCell.addEventListener('touchend', handleTouchEnd);
            dayCell.addEventListener('touchmove', handleTouchMove);
            dayCell.addEventListener('touchcancel', handleTouchCancel);
        });
    }

    addEventListenersToCells();
    calendar.on('datesSet', addEventListenersToCells);

    function handleTouchStart(e) {
        isTouch = true;
        console.log("Touch start detected");
        longPressTriggered = false;
        touchMoveDetected = false;
        const targetDate = e.target.closest('.fc-daygrid-day, .fc-timegrid-slot')?.getAttribute('data-date');
        longPressTimeout = setTimeout(function() {
            if (!touchMoveDetected) {
                longPressTriggered = true;
                console.log("Long press detected");
                if (targetDate) {
                    openLongPressModal(targetDate);
                }
            }
        }, clickTimeThreshold);
    }

    function handleTouchEnd(e) {
        console.log("Touch end detected");
        clearTimeout(longPressTimeout);
        if (!longPressTriggered && !longPressModalOpen && !touchMoveDetected) {
            console.log("Short press detected");
            const clickedDate = e.target.closest('.fc-daygrid-day, .fc-timegrid-slot')?.getAttribute('data-date');
            if (clickedDate) {
                openDayModal(clickedDate);
            }
        } else if (longPressTriggered) {
            console.log("Long press ended");
        }
        longPressTriggered = false;
        isTouch = false;
    }

    function handleTouchMove(e) {
        console.log("Touch move detected");
        touchMoveDetected = true;
        clearTimeout(longPressTimeout);
        console.log("Touch move detected, long press canceled");
    }

    function handleTouchCancel(e) {
        console.log("Touch cancel detected");
        clearTimeout(longPressTimeout);
        console.log("Touch canceled, long press canceled");
        isTouch = false;
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
        if (longPressModalOpen) return;

        const start = moment.tz(event.start, 'Asia/Tokyo').toDate();
        const end = event.end ? moment.tz(event.end, 'Asia/Tokyo').toDate() : null;

        document.getElementById('eventDetailName').innerText = event.title;
        document.getElementById('eventDetailStart').innerText = moment(start).format('YYYY/MM/DD HH:mm');
        document.getElementById('eventDetailStart').setAttribute('data-datetime', moment(start).format('YYYY-MM-DDTHH:mm'));
        document.getElementById('eventDetailEnd').innerText = end ? moment(end).format('YYYY/MM/DD HH:mm') : 'なし';
        if (end) {
            document.getElementById('eventDetailEnd').setAttribute('data-datetime', moment(end).format('YYYY-MM-DDTHH:mm'));
        }
        document.getElementById('eventDetailMemo').innerText = event.extendedProps.memo || 'なし';

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

    function closeLongPressModal() {
        document.getElementById('longPressModal').classList.add('hidden');
        document.getElementById('calendar-overlay').classList.add('hidden');
        longPressModalOpen = false;
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
    window.closeLongPressModal = closeLongPressModal;
});