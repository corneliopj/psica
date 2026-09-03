import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import ptBrLocale from '@fullcalendar/core/locales/pt-br';

document.addEventListener('DOMContentLoaded', function () {
    const calendarElement = document.getElementById('public-calendar');
    if (!calendarElement) return;

    const statusElement = document.getElementById('public-calendar-status');
    const timeOptionsElement = document.getElementById('public-time-options');
    const scheduledInput = document.getElementById('public_scheduled_at');
    let availableSlots = [];
    let selectedSlotId = null;

    function dateKey(date) {
        return [date.getFullYear(), date.getMonth() + 1, date.getDate()]
            .map((part) => String(part).padStart(2, '0'))
            .join('-');
    }

    function formatTime(value) {
        return new Intl.DateTimeFormat('pt-BR', {
            hour: '2-digit',
            minute: '2-digit',
        }).format(new Date(value));
    }

    function formatDate(value) {
        return new Intl.DateTimeFormat('pt-BR', {
            weekday: 'long',
            day: '2-digit',
            month: 'long',
        }).format(value);
    }

    function localInputValue(value) {
        const date = new Date(value);
        const offset = date.getTimezoneOffset() * 60000;
        return new Date(date.getTime() - offset).toISOString().slice(0, 16);
    }

    function selectSlot(button, slot, date) {
        timeOptionsElement.querySelectorAll('button').forEach((item) => item.classList.remove('border-indigo-600', 'bg-indigo-50', 'text-indigo-700'));
        button.classList.add('border-indigo-600', 'bg-indigo-50', 'text-indigo-700');
        scheduledInput.value = localInputValue(slot.start);
        selectedSlotId = slot.id;
        statusElement.textContent = `${formatDate(date)} às ${formatTime(slot.start)} selecionado.`;
    }

    function renderTimes(date) {
        const selectedDate = dateKey(date);
        const slotsForDate = availableSlots.filter((slot) => dateKey(new Date(slot.start)) === selectedDate);
        timeOptionsElement.innerHTML = '';
        selectedSlotId = null;
        scheduledInput.value = '';

        if (!slotsForDate.length) {
            statusElement.textContent = 'Nenhum horário disponível neste dia.';
            return;
        }

        statusElement.textContent = `Horários disponíveis em ${formatDate(date)}:`;
        slotsForDate.forEach((slot) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = formatTime(slot.start);
            button.className = 'rounded border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-indigo-600 hover:text-indigo-700';
            button.addEventListener('click', () => selectSlot(button, slot, date));
            timeOptionsElement.appendChild(button);

            if (selectedSlotId === null && (!scheduledInput.value || scheduledInput.value === localInputValue(slot.start))) {
                selectSlot(button, slot, date);
            }
        });
    }

    fetch('/api/slots')
        .then((response) => {
            if (!response.ok) throw new Error('Não foi possível carregar os horários.');
            return response.json();
        })
        .then((slots) => {
            availableSlots = slots.filter((slot) => slot.status === 'free');
            calendar.render();
            if (availableSlots.length) {
                renderTimes(new Date(availableSlots[0].start));
            } else {
                statusElement.textContent = 'Ainda não há horários disponíveis.';
            }
        })
        .catch(() => {
            statusElement.textContent = 'Não foi possível carregar os horários. Tente novamente.';
        });

    const calendar = new Calendar(calendarElement, {
        plugins: [dayGridPlugin, interactionPlugin],
        locale: 'pt-br',
        locales: [ptBrLocale],
        firstDay: 1,
        initialView: 'dayGridMonth',
        height: 'auto',
        events: (_fetchInfo, successCallback) => {
            successCallback(availableSlots.map((slot) => ({
                id: `public-slot-${slot.id}`,
                title: formatTime(slot.start),
                start: slot.start,
                end: slot.end,
                allDay: false,
                color: '#16a34a',
                textColor: '#ffffff',
            })));
        },
        dateClick: (info) => renderTimes(info.date),
        eventClick: (info) => {
            info.jsEvent.preventDefault();
            renderTimes(info.event.start);
        },
        dayCellClassNames: (info) => availableSlots.some((slot) => dateKey(new Date(slot.start)) === dateKey(info.date)) ? ['has-availability'] : [],
    });
});
