import DeleteIcon from '@mui/icons-material/Delete';
import EditIcon from '@mui/icons-material/Edit';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import IconButton from '@mui/material/IconButton';
import Typography from '@mui/material/Typography';
import { useEffect, useState } from 'react';
import api from '../../api/client';
import VehicleForm from './VehicleForm';

export default function VehicleList() {
  const [bookings, setBookings] = useState([]);
  const [openForm, setOpenForm] = useState(false);
  const [editing, setEditing] = useState(null);

  useEffect(() => { fetchBookings(); }, []);

  async function fetchBookings() {
    try {
      const res = await api.get('/api/vehicle-bookings');
      setBookings(res.data || []);
    } catch (e) { console.error(e); }
  }

  return (
    <Box>
      <Typography variant="h5" mb={2}>Vehicle Bookings</Typography>
      <Box sx={{ display: 'flex', gap: 1, mb: 2 }}>
        <Button variant="contained" onClick={()=>{ setEditing(null); setOpenForm(true); }}>New Booking</Button>
      </Box>
      <div>
        {bookings.length === 0 && <Typography>No bookings yet.</Typography>}
        {bookings.map(b => (
          <Box key={b.id} sx={{ p:1, border: '1px solid #eee', mb:1, display: 'flex', justifyContent: 'space-between' }}>
            <Box>
              <Typography>{b.purpose || b.subject || 'No purpose'}</Typography>
              <Typography variant="caption">{b.start_time} - {b.end_time}</Typography>
            </Box>
            <Box>
              <IconButton size="small" onClick={()=>{ setEditing(b); setOpenForm(true); }}><EditIcon fontSize="small"/></IconButton>
              <IconButton size="small" onClick={async ()=>{ if(!confirm('Delete?')) return; try{ await api.delete(`/api/vehicle-bookings/${b.id}`); fetchBookings(); }catch(e){alert('Delete failed')} }}><DeleteIcon fontSize="small"/></IconButton>
            </Box>
          </Box>
        ))}
      </div>
      <VehicleForm open={openForm} booking={editing} onClose={()=>{ setOpenForm(false); setEditing(null); }} onCreated={fetchBookings} onUpdated={fetchBookings} />
    </Box>
  );
}
