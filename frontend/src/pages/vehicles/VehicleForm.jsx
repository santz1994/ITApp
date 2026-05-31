import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogTitle from '@mui/material/DialogTitle';
import FormControl from '@mui/material/FormControl';
import InputLabel from '@mui/material/InputLabel';
import MenuItem from '@mui/material/MenuItem';
import Select from '@mui/material/Select';
import TextField from '@mui/material/TextField';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { DateTimePicker } from '@mui/x-date-pickers/DateTimePicker';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import dayjs from 'dayjs';
import { useEffect, useState } from 'react';
import api from '../../api/client';

export default function VehicleForm({ open, onClose, onCreated, onUpdated, booking }) {
  const [purpose, setPurpose] = useState('');
  const [vehicle, setVehicle] = useState('');
  const [vehicles, setVehicles] = useState([]);
  const [start, setStart] = useState(dayjs().startOf('hour'));
  const [end, setEnd] = useState(dayjs().add(1, 'hour').startOf('hour'));
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    let mounted = true;
    (async () => {
      try {
        const res = await api.get('/api/vehicles');
        if (mounted && Array.isArray(res.data)) setVehicles(res.data);
      } catch (e) {
        // ignore
      }
    })();
    return () => { mounted = false; };
  }, []);

  useEffect(() => {
    if (booking) {
      setPurpose(booking.purpose || booking.subject || '');
      setVehicle(booking.vehicle_id || booking.vehicle || '');
      setStart(booking.start_time ? dayjs(booking.start_time) : dayjs().startOf('hour'));
      setEnd(booking.end_time ? dayjs(booking.end_time) : dayjs().add(1, 'hour').startOf('hour'));
    }
  }, [booking]);

  async function handleSubmit(e) {
    e.preventDefault();
    setError(null);
    if (!purpose) return setError('Purpose is required');
    if (!start || !end || dayjs(end).isBefore(dayjs(start))) return setError('End must be after start');
    setLoading(true);
    try {
      const payload = {
        purpose,
        vehicle_id: vehicle || null,
        start_time: dayjs(start).toISOString(),
        end_time: dayjs(end).toISOString(),
      };
      if (booking && booking.id) {
        await api.put(`/api/vehicle-bookings/${booking.id}`, payload);
        if (onUpdated) onUpdated();
      } else {
        await api.post('/api/vehicle-bookings', payload);
        if (onCreated) onCreated();
      }
      setLoading(false);
      handleClose();
    } catch (err) {
      setLoading(false);
      setError(err.response?.data?.message || 'Failed to save booking');
    }
  }

  function handleClose() {
    setPurpose(''); setVehicle(''); setStart(dayjs().startOf('hour')); setEnd(dayjs().add(1, 'hour').startOf('hour')); setError(null);
    if (onClose) onClose();
  }

  return (
    <Dialog open={open} onClose={handleClose} fullWidth maxWidth="sm">
      <DialogTitle>Vehicle Booking</DialogTitle>
      <form onSubmit={handleSubmit}>
        <DialogContent>
          <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
            <TextField label="Purpose" value={purpose} onChange={(e)=>setPurpose(e.target.value)} fullWidth />
            <FormControl fullWidth>
              <InputLabel id="vehicle-label">Vehicle</InputLabel>
              <Select labelId="vehicle-label" label="Vehicle" value={vehicle} onChange={(e)=>setVehicle(e.target.value)}>
                <MenuItem value="">None</MenuItem>
                {vehicles.map(v => <MenuItem key={v.id} value={v.id}>{v.name || v.reg_no || `Vehicle ${v.id}`}</MenuItem>)}
              </Select>
            </FormControl>
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
