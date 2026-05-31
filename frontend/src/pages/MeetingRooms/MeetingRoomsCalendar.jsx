import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import FullCalendar from '@fullcalendar/react';
import timeGridPlugin from '@fullcalendar/timegrid';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import { useEffect, useState } from 'react';
import api from '../../api/client';
import MeetingRoomForm from './MeetingRoomForm';

// Calendar supports click-to-create (select) and event-click to edit

export default function MeetingRoomsCalendar() {
    const [events, setEvents] = useState([]);
    const [openForm, setOpenForm] = useState(false);
    const [editing, setEditing] = useState(null);

    useEffect(() => {
        fetchEvents();
    }, []);

    async function fetchEvents() {
        try {
            const res = await api.get('/api/meeting-room-bookings-calendar/data');
            // expecting [{ id, title, start, end }]
            setEvents(res.data || []);
        } catch (err) {
            console.error('Failed to load calendar data', err);
        }
    }

    function handleDateSelect(selectInfo) {
        const start = selectInfo.startStr;
        const end = selectInfo.endStr;
        setEditing({ start_time: start, end_time: end });
        setOpenForm(true);
    }

    function handleEventClick(clickInfo) {
        const id = clickInfo.event.id;
        const found = events.find(e => String(e.id) === String(id));
        if (found) {
            setEditing(found);
            setOpenForm(true);
        } else {
            // fallback: open form with id only
            setEditing({ id });
            setOpenForm(true);
        }
    }

    // handle drag (move) and resize
    async function handleEventChange(changeInfo) {
        const ev = changeInfo.event;
        const id = ev.id;
        const payload = {
            start_time: ev.start?.toISOString(),
            end_time: ev.end?.toISOString(),
        };
        try {
            await api.put(`/api/meeting-room-bookings/${id}`, payload);
            fetchEvents();
        } catch (e) {
            console.error('Update failed', e);
            alert('Failed to update booking time');
            fetchEvents();
        }
    }

    function renderEventContent(arg) {
        // arg.event.extendedProps may contain room info
        const title = arg.event.title;
        const room = arg.event.extendedProps?.room_name || arg.event.extendedProps?.room || '';
        return (
            <div style={{ padding: '2px 4px' }}>
                <div style={{ fontSize: 12, fontWeight: 600 }}>{title}</div>
                {room && <div style={{ fontSize: 11, opacity: 0.85 }}>{room}</div>}
            </div>
        );
    }

    return (
        <Box>
            <Typography variant="h5" mb={2}>Meeting Room Calendar</Typography>
            <FullCalendar
                plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
                initialView="timeGridWeek"
                headerToolbar={{ left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' }}
                events={events}
                height="auto"
                selectable={true}
                select={handleDateSelect}
                eventClick={handleEventClick}
                editable={true}
                eventDrop={handleEventChange}
                eventResize={handleEventChange}
                eventContent={renderEventContent}
            />
            <MeetingRoomForm open={openForm} booking={editing} onClose={() => { setOpenForm(false); setEditing(null); }} onCreated={fetchEvents} onUpdated={fetchEvents} />
        </Box>
    );
}
