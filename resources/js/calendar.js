import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        events: JSON.parse(calendarEl.dataset.events),
        dateClick: function(info) {
            openModal(info.dateStr);
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
});

function openModal(date) {
    document.getElementById('start_date').value = date;
    document.getElementById('end_date').value = date;
    document.getElementById('eventModal').classList.remove('hidden');
    document.getElementById('name').focus();  // ポップアップを開いたときにフォーカスを設定
}

function closeModal() {
    document.getElementById('eventModal').classList.add('hidden');
}

window.openModal = openModal;
window.closeModal = closeModal;