import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/client';
import MeetingRoomForm from './MeetingRoomForm';

export default function MeetingRoomsList() {
  const [bookings, setBookings] = useState([]);
  const [openForm, setOpenForm] = useState(false);

  useEffect(() => {
    fetchBookings();
  }, []);

  async function fetchBookings() {
    try {
      const res = await api.get('/api/meeting-room-bookings');
      setBookings(res.data || []);
    } catch (err) {
      console.error('Failed to load bookings', err);
    }
  }

  return (
    <Box>
      <Typography variant="h5" mb={2}>Meeting Room Bookings</Typography>
      <Box sx={{ display: 'flex', gap: 1, mb: 2 }}>
        <Button variant="contained" onClick={() => setOpenForm(true)}>New Booking</Button>
        <Button variant="outlined" component={Link} to="/meeting-rooms/calendar">Open Calendar</Button>
      </Box>
      <div>
        {bookings.length === 0 && <Typography>No bookings yet.</Typography>}
        {bookings.map((b) => (
          <Box key={b.id} sx={{ p: 1, border: '1px solid #eee', mb: 1 }}>
            <Typography>{b.subject || 'No subject'}</Typography>
            <Typography variant="caption">{b.start_time} - {b.end_time}</Typography>
          </Box>
        ))}
      </div>
      <MeetingRoomForm open={openForm} onClose={() => setOpenForm(false)} onCreated={fetchBookings} />
    </Box>
  );
}
