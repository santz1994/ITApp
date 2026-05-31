import Box from '@mui/material/Box';
import Chip from '@mui/material/Chip';
import Typography from '@mui/material/Typography';
import dayjs from 'dayjs';
import { useEffect, useState } from 'react';
import api from '../../services/api';

const STATUS_COLORS = {
  pending: '#ed6c02',
  approved: '#2e7d32',
  finished: '#0288d1',
  blocked: '#9c27b0',
};

const ROOMS = ['Ruang Meeting 1', 'Ruang Meeting 2', 'Ruang Meeting 3'];

export default function LcdDashboard() {
  const [bookings, setBookings] = useState([]);
  const [now, setNow] = useState(dayjs());

  const fetchBookings = async () => {
    try {
      const res = await api.get('/meeting-room-bookings', { params: { date: dayjs().format('YYYY-MM-DD'), per_page: 200 } });
      setBookings(res.data.data || []);
    } catch (err) { console.error(err); }
  };

  useEffect(() => {
    fetchBookings();
    const interval = setInterval(() => {
      setNow(dayjs());
      fetchBookings();
    }, 60000); // Refresh every 60s
    return () => clearInterval(interval);
  }, []);

  const getRoomBookings = (roomName) =>
    bookings.filter(b => b.room_name === roomName && b.status !== 'cancelled');

  return (
    <Box sx={{
      minHeight: '100vh', bgcolor: '#0a1628', color: '#fff', p: 4,
      display: 'flex', flexDirection: 'column',
    }}>
      {/* Header */}
      <Box sx={{ textAlign: 'center', mb: 4 }}>
        <Typography variant="h3" fontWeight={700} sx={{ color: '#4fc3f7', letterSpacing: 2 }}>
          📋 Meeting Room Schedule
        </Typography>
        <Typography variant="h6" sx={{ color: '#90caf9', mt: 1 }}>
          {now.format('dddd, DD MMMM YYYY')} — {now.format('HH:mm')}
        </Typography>
      </Box>

      {/* Room Grid */}
      <Box sx={{ display: 'flex', gap: 3, flex: 1, overflow: 'auto' }}>
        {ROOMS.map(room => {
          const roomBookings = getRoomBookings(room);
          return (
            <Box key={room} sx={{
              flex: 1, bgcolor: '#132039', borderRadius: 3, p: 3,
              border: '1px solid #1e3a5f', display: 'flex', flexDirection: 'column',
            }}>
              <Typography variant="h5" fontWeight={700} sx={{ color: '#4fc3f7', mb: 2, textAlign: 'center' }}>
                {room}
              </Typography>

              {roomBookings.length === 0 ? (
                <Box sx={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <Typography variant="h6" sx={{ color: '#546e7a' }}>No bookings today</Typography>
                </Box>
              ) : (
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 1.5, flex: 1, overflow: 'auto' }}>
                  {roomBookings.map(b => {
                    const color = STATUS_COLORS[b.status] || '#757575';
                    const isActive = b.status === 'approved' && dayjs().isBetween(dayjs(b.start_datetime), dayjs(b.end_datetime));
                    return (
                      <Box key={b.id} sx={{
                        p: 2, borderRadius: 2, bgcolor: isActive ? '#1b5e20' : '#1a2744',
                        borderLeft: `4px solid ${color}`,
                        boxShadow: isActive ? '0 0 20px rgba(76,175,80,0.3)' : 'none',
                      }}>
                        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                          <Typography variant="subtitle1" fontWeight={700} noWrap sx={{ color: '#fff', flex: 1 }}>
                            {b.subject}
                          </Typography>
                          <Chip label={b.status.toUpperCase()} size="small"
                            sx={{ bgcolor: color, color: '#fff', fontWeight: 700, fontSize: 10 }} />
                        </Box>
                        <Typography variant="body2" sx={{ color: '#90caf9', mt: 0.5 }}>
                          🕐 {dayjs(b.start_datetime).format('HH:mm')} – {dayjs(b.end_datetime).format('HH:mm')}
                        </Typography>
                        {b.user?.name && (
                          <Typography variant="body2" sx={{ color: '#607d8b', mt: 0.5 }}>
                            👤 {b.user.name}
                          </Typography>
                        )}
                      </Box>
                    );
                  })}
                </Box>
              )}
            </Box>
          );
        })}
      </Box>

      {/* Footer */}
      <Box sx={{ textAlign: 'center', mt: 3, pt: 2, borderTop: '1px solid #1e3a5f' }}>
        <Typography variant="body2" sx={{ color: '#546e7a' }}>
          🔄 Auto-refresh every 60 seconds — PT Quty Karunia
        </Typography>
      </Box>
    </Box>
  );
}
