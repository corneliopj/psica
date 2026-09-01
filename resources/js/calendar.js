import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import ptBrLocale from '@fullcalendar/core/locales/pt-br';

document.addEventListener('DOMContentLoaded', function(){
    const el = document.getElementById('calendar');
    if(!el) return;

    const calendar = new Calendar(el, {
        plugins: [ dayGridPlugin, timeGridPlugin, interactionPlugin ],
        locale: 'pt-br',
        locales: [ ptBrLocale ],
        firstDay: 1,
        slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        initialView: 'timeGridWeek',
        selectable: true,
        selectAllow: function(selectInfo) {
            // prevent selecting if it overlaps any busy/background events
            const start = selectInfo.start;
            const end = selectInfo.end;
            const events = calendar.getEvents();
            for(const ev of events){
                // only consider background/busy events
                if(ev.display !== 'background') continue;
                const evStart = ev.start;
                const evEnd = ev.end || new Date(evStart.getTime() + 30*60000);
                if(start < evEnd && end > evStart) return false;
            }
            return true;
        },
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
            // mark scheduled input as fixed (opened from calendar) so user can't edit it
            scheduledInput.readOnly = true;
            scheduledInput.dataset.fixed = '1';
            scheduledInput.classList.add('bg-gray-100', 'cursor-not-allowed');
            // show Brazilian formatted display and hide the editable input
            const display = document.getElementById('booking_scheduled_display');
            if(display){
                display.innerText = formatBR(dt);
                display.classList.remove('hidden');
                scheduledInput.classList.add('hidden');
            }
            nameInput.value = '';
            phoneInput.value = '';
            errorBox.innerText = '';
            modal.classList.remove('hidden');
            // focus name
            nameInput.focus();
        },
        // fetch events and also create background "busy" events for visual blocking
        events: function(fetchInfo, successCallback, failureCallback){
            fetch('/api/agendamentos')
                .then(r => r.json())
                .then(js => {
                    const out = [];
                    for(const e of js){
                        // assume backend provides start and end (or scheduled_at and duration)
                        out.push(e);
                        // create a background event to indicate occupied slot
                        const bg = Object.assign({}, e);
                        bg.id = 'busy-' + (e.id ?? Math.random().toString(36).slice(2,9));
                        bg.display = 'background';
                        bg.color = '#f87171';
                        out.push(bg);
                    }
                    successCallback(out);
                }).catch(err => failureCallback(err));
        }
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
                // add event to calendar and add a background busy block
                calendar.addEvent({ id: js.event.id, title: name, start: js.event.scheduled_at, end: js.event.ends_at ?? js.event.end });
                const busyId = 'busy-' + js.event.id;
                calendar.addEvent({ id: busyId, display: 'background', start: js.event.scheduled_at, end: js.event.ends_at ?? js.event.end, color: '#f87171' });
                // close modal
                const modalEl = document.getElementById('bookingModal');
                // if scheduled input was fixed, reset readonly state and restore visible input
                const scheduledInputEl = document.getElementById('booking_scheduled_at');
                if(scheduledInputEl && scheduledInputEl.dataset.fixed){
                    scheduledInputEl.readOnly = false;
                    delete scheduledInputEl.dataset.fixed;
                    scheduledInputEl.classList.remove('bg-gray-100', 'cursor-not-allowed');
                    const display = document.getElementById('booking_scheduled_display');
                    if(display){ display.classList.add('hidden'); scheduledInputEl.classList.remove('hidden'); }
                }
                modalEl.classList.add('hidden');
            }).catch(err => {
                errorBox.innerText = 'Erro ao conectar';
            });
        });
    }
    // Modal cancel/close
    const bookingCancel = document.getElementById('booking_cancel');
    if(bookingCancel){
        bookingCancel.addEventListener('click', function(){
            const scheduledInputEl = document.getElementById('booking_scheduled_at');
            if(scheduledInputEl && scheduledInputEl.dataset.fixed){
                scheduledInputEl.readOnly = false;
                delete scheduledInputEl.dataset.fixed;
                scheduledInputEl.classList.remove('bg-gray-100', 'cursor-not-allowed');
                const display = document.getElementById('booking_scheduled_display');
                if(display){ display.classList.add('hidden'); scheduledInputEl.classList.remove('hidden'); }
            }
            document.getElementById('bookingModal').classList.add('hidden');
        });
    }

    function pad(n){ return n<10 ? '0'+n : ''+n }
    function formatBR(d){
        const day = pad(d.getDate());
        const month = pad(d.getMonth()+1);
        const year = d.getFullYear();
        const hours = pad(d.getHours());
        const mins = pad(d.getMinutes());
        return `${day}/${month}/${year} ${hours}:${mins}`;
    }
});
