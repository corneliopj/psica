import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import ptBrLocale from '@fullcalendar/core/locales/pt-br';

document.addEventListener('DOMContentLoaded', function(){
    const el = document.getElementById('calendar');
    if(!el) return;
    const perfil = el.dataset.perfil || 'publico';
    const podeGerenciarSlots = perfil === 'profissional' || perfil === 'admin';

    function localDateTimeValue(date) {
        return new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    }

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
            const localISO = localDateTimeValue(dt);
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
        // fetch agendamentos and slots and combine events
        events: function(fetchInfo, successCallback, failureCallback){
            Promise.all([
                fetch('/api/agendamentos').then(r => r.json()),
                fetch('/api/slots').then(r => r.json())
            ]).then(([agendamentos, slots]) => {
                const out = [];
                // push appointments
                for(const e of agendamentos){ out.push(e); }
                // push slots as background events: free=green, occupied=red
                for(const s of slots){
                    const bg = {
                        id: 'slot-' + s.id,
                        start: s.start,
                        end: s.end,
                        display: 'background',
                        color: s.status === 'free' ? '#34d399' : '#f87171',
                        extendedProps: { slotId: s.id, status: s.status }
                    };
                    out.push(bg);
                    // if on dashboard (analyst), add an interactive event on top to allow occupy/release
                    if(podeGerenciarSlots){
                        out.push({
                            id: 'slot-ui-' + s.id,
                            start: s.start,
                            end: s.end,
                            title: s.status === 'free' ? 'Livre' : 'Ocupado',
                            color: s.status === 'free' ? '#059669' : '#dc2626',
                            extendedProps: { slotId: s.id, status: s.status }
                        });
                    }
                }
                successCallback(out);
            }).catch(err => failureCallback(err));
        },
        // limit visible hours to 14:00-21:00
        slotMinTime: '14:00:00',
        slotMaxTime: '21:00:00',
    });

    calendar.render();
    // allow clicking interactive slot events to toggle status (dashboard only)
        if(podeGerenciarSlots){
        calendar.setOption('eventClick', function(info){
            const props = info.event.extendedProps || {};
            if(props.canConfirm){
                if(!confirm('Confirmar esta sessão solicitada?')) return;
                fetch('/agendamentos/' + info.event.id + '/confirmar', {
                    method: 'PATCH',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                }).then(async r => {
                    if(!r.ok){ alert('Erro ao confirmar sessão'); return; }
                    await r.json();
                    calendar.refetchEvents();
                }).catch(() => { alert('Erro de rede'); });
                return;
            }
            const slotId = props.slotId;
            if(!slotId) return;
            const current = props.status;
            const next = current === 'free' ? 'occupied' : 'free';
            const verb = next === 'occupied' ? 'ocupar' : 'liberar';
            if(!confirm(`Deseja ${verb} este horário?`)) return;
            fetch('/slots/' + slotId, {
                method: 'PATCH',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ status: next })
            }).then(async r => {
                if(!r.ok){ alert('Erro ao atualizar slot'); return }
                await r.json();
                calendar.refetchEvents();
            }).catch(err => { alert('Erro de rede'); });
        });
    }
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

    // Slot modal handlers (analyst)
    const addSlotBtn = document.getElementById('addSlotBtn');
    const slotModal = document.getElementById('slotModal');
    const slotForm = document.getElementById('slot_form');
    if(addSlotBtn && slotModal && podeGerenciarSlots){
        addSlotBtn.addEventListener('click', function(){
            const now = new Date();
            const rounded = new Date(now);
            rounded.setMinutes(0, 0, 0);
            const end = new Date(rounded.getTime() + 60 * 60000);
            const dateValue = localDateTimeValue(rounded).slice(0, 10);
            document.getElementById('slot_date').value = dateValue;
            document.getElementById('slot_start_time').value = localDateTimeValue(rounded).slice(11, 16);
            document.getElementById('slot_end_time').value = localDateTimeValue(end).slice(11, 16);
            document.getElementById('slot_error').innerText = '';
            slotModal.classList.remove('hidden');
        });
        document.getElementById('slot_cancel').addEventListener('click', function(){ slotModal.classList.add('hidden'); });
    }
    if(slotForm && podeGerenciarSlots){
        slotForm.addEventListener('submit', function(e){
            e.preventDefault();
            const date = document.getElementById('slot_date').value;
            const startTime = document.getElementById('slot_start_time').value;
            const endTime = document.getElementById('slot_end_time').value;
            const repeat_until = document.getElementById('slot_repeat_until').value || null;
            const errorBox = document.getElementById('slot_error');
            const start = `${date}T${startTime}`;
            const end = `${date}T${endTime}`;
            errorBox.innerText = '';
            fetch('/slots', {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ start: start, end: end, repeat_weekly: repeat_until ? 1 : 0, repeat_until: repeat_until })
            }).then(async r => {
                const js = await r.json().catch(() => ({}));
                if(!r.ok){
                    errorBox.innerText = js.message || (js.errors ? Object.values(js.errors).flat().join('. ') : 'Erro ao criar slot');
                    return;
                }
                calendar.refetchEvents();
                slotModal.classList.add('hidden');
            }).catch(() => { errorBox.innerText = 'Erro de rede'; });
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
