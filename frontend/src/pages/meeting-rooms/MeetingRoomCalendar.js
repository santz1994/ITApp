import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import FullCalendar from '@fullcalendar/react';
import timeGridPlugin from '@fullcalendar/timegrid';
import AddIcon from '@mui/icons-material/Add';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Chip from '@mui/material/Chip';
import Typography from '@mui/material/Typography';
import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../services/api';

const statusColors = {
  pending: '#ed6c02',
  approved: '#2e7d32',
  rejected: '#d32f2f',
  finished: '#0288d1',
  cancelled: '#757575',
  blocked: '#9c27b0',
};

function MeetingRoomCalendar() {
  const navigate = useNavigate();
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get('/meeting-room-bookings-calendar/data').then((res) => {
      const data = res.data.data || [];
      setEvents(data.map((b) => ({
        id: b.id,
        title: `${b.room_name || 'Room'} — ${b.subject || '-'}`,
        start: b.start_datetime,
        end: b.end_datetime,
        color: statusColors[b.status] || '#757575',
        extendedProps: { status: b.status, room: b.room_name, user: b.user?.name },
      })));
      setLoading(false);
    }).catch(() => setLoading(false));
  }, []);

  const handleEventClick = (info) => {
    navigate(`/meeting-rooms/${info.event.id}`);
  };

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 3 }}>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          <Button startIcon={<ArrowBackIcon />} onClick={() => navigate('/meeting-rooms')}>Kembali</Button>
          <Typography variant="h5" fontWeight={700}>Kalender Ruang Meeting</Typography>
        </Box>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => navigate('/meeting-rooms/create')}>Booking Baru</Button>
      </Box>

      {/* Legend */}
      <Box sx={{ display: 'flex', gap: 1, mb: 2, flexWrap: 'wrap' }}>
        {Object.entries(statusColors).map(([status, color]) => (
          <Chip key={status} label={status.charAt(0).toUpperCase() + status.slice(1)} size="small" sx={{ bgcolor: color, color: 'white', fontWeight: 500 }} />
        ))}
      </Box>

      <Card>
        <CardContent sx={{ p: { xs: 1, sm: 2, md: 3 } }}>
          <FullCalendar
            plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
            initialView="dayGridMonth"
            headerToolbar={{ left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' }}
            events={events}
            eventClick={handleEventClick}
            height={650}
            locale="id"
            buttonText={{ today: 'Hari Ini', month: 'Bulan', week: 'Minggu', day: 'Hari' }}
            slotMinTime="07:00:00"
            slotMaxTime="21:00:00"
            nowIndicator={true}
            eventDisplay="block"
            dayMaxEvents={3}
          />
        </CardContent>
      </Card>
    </Box>
  );
}

export default MeetingRoomCalendar;
