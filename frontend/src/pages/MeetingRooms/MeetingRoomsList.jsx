import DeleteIcon from '@mui/icons-material/Delete';
import EditIcon from '@mui/icons-material/Edit';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import IconButton from '@mui/material/IconButton';
import Typography from '@mui/material/Typography';
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/client';
import MeetingRoomForm from './MeetingRoomForm';

export default function MeetingRoomsList() {
  const [bookings, setBookings] = useState([]);
  const [openForm, setOpenForm] = useState(false);
  const [editing, setEditing] = useState(null);

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
          <Button variant="contained" onClick={() => { setEditing(null); setOpenForm(true); }}>New Booking</Button>
        <Button variant="outlined" component={Link} to="/meeting-rooms/calendar">Open Calendar</Button>
      </Box>
      <div>
        {bookings.length === 0 && <Typography>No bookings yet.</Typography>}
        {bookings.map((b) => (
          <Box key={b.id} sx={{ p: 1, border: '1px solid #eee', mb: 1, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
            <Box>
              <Typography>{b.subject || 'No subject'}</Typography>
              <Typography variant="caption">{b.start_time} - {b.end_time}</Typography>
            </Box>
            <Box>
              <IconButton aria-label="edit" size="small" onClick={()=>{ setEditing(b); setOpenForm(true); }}>
                <EditIcon fontSize="small" />
              </IconButton>
              <IconButton aria-label="delete" size="small" onClick={async ()=>{
                if (!confirm('Delete this booking?')) return;
                try { await api.delete(`/api/meeting-room-bookings/${b.id}`); fetchBookings(); } catch (e) { console.error(e); alert('Delete failed'); }
              }}>
                <DeleteIcon fontSize="small" />
              </IconButton>
            </Box>
          </Box>
        ))}
      </div>
      <MeetingRoomForm open={openForm} onClose={() => setOpenForm(false)} onCreated={fetchBookings} />
      <MeetingRoomForm open={openForm} booking={editing} onClose={() => { setOpenForm(false); setEditing(null); }} onCreated={fetchBookings} onUpdated={fetchBookings} />
    </Box>
  );
}
