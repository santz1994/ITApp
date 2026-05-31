import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogTitle from '@mui/material/DialogTitle';
import TextField from '@mui/material/TextField';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { DateTimePicker } from '@mui/x-date-pickers/DateTimePicker';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import dayjs from 'dayjs';
import { useState } from 'react';
import api from '../../api/client';

export default function MeetingRoomForm({ open, onClose, onCreated }) {
  const [subject, setSubject] = useState('');
  const [room, setRoom] = useState('');
  const [start, setStart] = useState(dayjs().startOf('hour'));
  const [end, setEnd] = useState(dayjs().add(1, 'hour').startOf('hour'));
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  async function handleSubmit(e) {
    e.preventDefault();
    setError(null);
    if (!subject) return setError('Subject is required');
    if (!start || !end || dayjs(end).isBefore(dayjs(start))) return setError('End must be after start');

    setLoading(true);
    try {
      const payload = {
        subject,
        room: room || null,
        start_time: dayjs(start).toISOString(),
        end_time: dayjs(end).toISOString(),
      };
      await api.post('/api/meeting-room-bookings', payload);
      setLoading(false);
      if (onCreated) onCreated();
      handleClose();
    } catch (err) {
      setLoading(false);
      setError(err.response?.data?.message || 'Failed to create booking');
    }
  }

  function handleClose() {
    setSubject(''); setRoom(''); setStart(dayjs().startOf('hour')); setEnd(dayjs().add(1, 'hour').startOf('hour')); setError(null);
    if (onClose) onClose();
  }

  return (
    <Dialog open={open} onClose={handleClose} maxWidth="sm" fullWidth>
      <DialogTitle>New Meeting Room Booking</DialogTitle>
      <form onSubmit={handleSubmit}>
        <DialogContent>
          <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
            <TextField label="Subject" value={subject} onChange={(e)=>setSubject(e.target.value)} fullWidth />
            <TextField label="Room" value={room} onChange={(e)=>setRoom(e.target.value)} fullWidth />
            <LocalizationProvider dateAdapter={AdapterDayjs}>
              <DateTimePicker label="Start" value={start} onChange={(v)=>setStart(v)} renderInput={(params)=><TextField {...params} />} />
              <DateTimePicker label="End" value={end} onChange={(v)=>setEnd(v)} renderInput={(params)=><TextField {...params} />} />
            </LocalizationProvider>
            {error && <Box sx={{ color: 'error.main' }}>{error}</Box>}
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleClose} disabled={loading}>Cancel</Button>
          <Button type="submit" variant="contained" disabled={loading}>{loading ? 'Saving...' : 'Save'}</Button>
        </DialogActions>
      </form>
    </Dialog>
  );
}
