import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import MeetingRoomIcon from '@mui/icons-material/MeetingRoom';
import SaveIcon from '@mui/icons-material/Save';
import Alert from '@mui/material/Alert';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardActions from '@mui/material/CardActions';
import CardContent from '@mui/material/CardContent';
import CircularProgress from '@mui/material/CircularProgress';
import Divider from '@mui/material/Divider';
import Grid from '@mui/material/Grid';
import MenuItem from '@mui/material/MenuItem';
import TextField from '@mui/material/TextField';
import Typography from '@mui/material/Typography';
import { useState } from 'react';
import { useDispatch } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { createMeetingRoomBooking } from '../../store/slices/meetingRoomSlice';

const meetingRooms = [
  { value: 'Ruang Meeting 1', label: 'Ruang Meeting 1' },
  { value: 'Ruang Meeting 2', label: 'Ruang Meeting 2' },
  { value: 'Ruang Meeting 3', label: 'Ruang Meeting 3' },
];

function MeetingRoomCreate() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [form, setForm] = useState({
    room_name: '', subject: '', description: '',
    start_datetime: '', end_datetime: '',
    attendees: '', equipment_request: '', consumption_request: '',
    is_blocking: false,
  });

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setForm((prev) => ({ ...prev, [name]: type === 'checkbox' ? checked : value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!form.room_name || !form.subject || !form.start_datetime || !form.end_datetime) {
      toast.warning('Lengkapi field yang wajib.');
      return;
    }
    setLoading(true);
    try {
      await dispatch(createMeetingRoomBooking(form)).unwrap();
      toast.success('Booking ruang meeting berhasil diajukan.');
      navigate('/meeting-rooms');
    } catch (err) {
      toast.error(err || 'Gagal membuat booking.');
    }
    setLoading(false);
  };

  return (
    <Box>
      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 3 }}>
        <Button startIcon={<ArrowBackIcon />} onClick={() => navigate('/meeting-rooms')}>Kembali</Button>
        <Typography variant="h5" fontWeight={700}>Booking Ruang Meeting</Typography>
      </Box>

      <Card>
        <Box component="form" onSubmit={handleSubmit}>
          <CardContent sx={{ p: 3 }}>
            <Grid container spacing={3}>
              <Grid item xs={12} md={6}>
                <Typography variant="subtitle1" fontWeight={600} sx={{ mb: 2 }}>
                  <MeetingRoomIcon sx={{ mr: 1, verticalAlign: 'middle', fontSize: 20 }} />
                  Informasi Booking
                </Typography>
                <TextField fullWidth select required label="Ruangan" name="room_name" value={form.room_name} onChange={handleChange} sx={{ mb: 2 }}>
                  {meetingRooms.map((r) => <MenuItem key={r.value} value={r.value}>{r.label}</MenuItem>)}
                </TextField>
                <TextField fullWidth required label="Subjek" name="subject" value={form.subject} onChange={handleChange} placeholder="e.g., Rapat Bulanan Departemen IT" sx={{ mb: 2 }} />
                <TextField fullWidth multiline rows={3} label="Deskripsi / Agenda" name="description" value={form.description} onChange={handleChange} sx={{ mb: 2 }} />
                <TextField fullWidth label="Jumlah Peserta" name="attendees" type="number" value={form.attendees} onChange={handleChange} inputProps={{ min: 1 }} />
              </Grid>

              <Grid item xs={12} md={6}>
                <Typography variant="subtitle1" fontWeight={600} sx={{ mb: 2 }}>Waktu & Kebutuhan</Typography>
                <TextField fullWidth required label="Waktu Mulai" name="start_datetime" type="datetime-local" value={form.start_datetime} onChange={handleChange} InputLabelProps={{ shrink: true }} sx={{ mb: 2 }} />
                <TextField fullWidth required label="Waktu Selesai" name="end_datetime" type="datetime-local" value={form.end_datetime} onChange={handleChange} InputLabelProps={{ shrink: true }} sx={{ mb: 2 }} />
                <TextField fullWidth multiline rows={2} label="Permintaan Peralatan" name="equipment_request" value={form.equipment_request} onChange={handleChange} placeholder="e.g., Projector, Whiteboard, Sound System" sx={{ mb: 2 }} />
                <TextField fullWidth multiline rows={2} label="Permintaan Konsumsi" name="consumption_request" value={form.consumption_request} onChange={handleChange} placeholder="e.g., Snack & Minuman untuk 10 orang" sx={{ mb: 2 }} />
              </Grid>
            </Grid>

            <Alert severity="info" sx={{ mt: 2 }}>
              Pastikan waktu booking minimal 15 menit dari sekarang. Sistem akan mencegah bentrok jadwal secara otomatis.
            </Alert>
          </CardContent>
          <Divider />
          <CardActions sx={{ p: 2, justifyContent: 'space-between' }}>
            <Button onClick={() => navigate('/meeting-rooms')}>Batal</Button>
            <Button type="submit" variant="contained" startIcon={loading ? <CircularProgress size={18} /> : <SaveIcon />} disabled={loading}>
              Ajukan Booking
            </Button>
          </CardActions>
        </Box>
      </Card>
    </Box>
  );
}

export default MeetingRoomCreate;
