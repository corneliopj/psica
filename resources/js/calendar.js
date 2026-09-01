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
            // open booking modal (simple prompt for demo)
            const name = prompt('Seu nome:');
            if(!name) return;
            const phone = prompt('Telefone:');
            if(!phone) return;

            fetch('/api/solicitar', {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({name:name, phone:phone, scheduled_at: info.startStr})
            }).then(r => r.json()).then(js => {
                if(js.error){ alert(js.error); }
                else { calendar.addEvent({ id: js.event.id, title: name, start: js.event.scheduled_at }); }
            });
        },
        events: '/api/agendamentos'
    });

    calendar.render();
});
