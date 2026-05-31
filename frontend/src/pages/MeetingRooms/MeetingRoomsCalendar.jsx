import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import FullCalendar from '@fullcalendar/react';
import timeGridPlugin from '@fullcalendar/timegrid';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import { useEffect, useState } from 'react';
import api from '../../api/client';

export default function MeetingRoomsCalendar() {
  const [events, setEvents] = useState([]);

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

  return (
    <Box>
      <Typography variant="h5" mb={2}>Meeting Room Calendar</Typography>
      <FullCalendar
        plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
        initialView="timeGridWeek"
        headerToolbar={{ left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' }}
        events={events}
        height="auto"
      />
    </Box>
  );
}
