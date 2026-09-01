import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', function(){
    const el = document.getElementById('calendar');
    if(!el) return;

    const calendar = new Calendar(el, {
        plugins: [ dayGridPlugin, timeGridPlugin, interactionPlugin ],
        initialView: 'timeGridWeek',
        selectable: true,
        select: function(info){
            // open booking modal (replace prompts)
            const modal = document.getElementById('bookingModal');
            const nameInput = document.getElementById('booking_name');
            const phoneInput = document.getElementById('booking_phone');
            const scheduledInput = document.getElementById('booking_scheduled_at');
            const errorBox = document.getElementById('booking_error');
            if(!modal || !nameInput || !phoneInput || !scheduledInput) {
                alert('Booking modal not found');
                return;
            }
            // set scheduled input (datetime-local expects YYYY-MM-DDTHH:MM)
            const dt = new Date(info.start);
            const localISO = new Date(dt.getTime() - dt.getTimezoneOffset()*60000).toISOString().slice(0,16);
            scheduledInput.value = localISO;
            nameInput.value = '';
            phoneInput.value = '';
            errorBox.innerText = '';
            modal.classList.remove('hidden');
            // focus name
            nameInput.focus();
        },
        events: '/api/agendamentos'
    });

    calendar.render();
    // Modal submit handler
    const bookingForm = document.getElementById('booking_form');
    if(bookingForm){
        bookingForm.addEventListener('submit', function(e){
            e.preventDefault();
            const name = document.getElementById('booking_name').value.trim();
            const phone = document.getElementById('booking_phone').value.trim();
            const scheduled_at = document.getElementById('booking_scheduled_at').value;
            const errorBox = document.getElementById('booking_error');
            errorBox.innerText = '';
            fetch('/api/solicitar', {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({name:name, phone:phone, scheduled_at: scheduled_at})
            }).then(async r => {
                const js = await r.json();
                if(!r.ok){
                    errorBox.innerText = js.error || (js.errors ? Object.values(js.errors).flat().join('. ') : 'Erro');
                    return;
                }
                // add event to calendar
                calendar.addEvent({ id: js.event.id, title: name, start: js.event.scheduled_at });
                // close modal
                document.getElementById('bookingModal').classList.add('hidden');
            }).catch(err => {
                errorBox.innerText = 'Erro ao conectar';
            });
        });
    }
    // Modal cancel/close
    const bookingCancel = document.getElementById('booking_cancel');
    if(bookingCancel){
        bookingCancel.addEventListener('click', function(){
            document.getElementById('bookingModal').classList.add('hidden');
        });
    }
});
