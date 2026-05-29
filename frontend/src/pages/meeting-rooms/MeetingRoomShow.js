import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import CancelIcon from '@mui/icons-material/Cancel';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Chip from '@mui/material/Chip';
import CircularProgress from '@mui/material/CircularProgress';
import Divider from '@mui/material/Divider';
import Grid from '@mui/material/Grid';
import TextField from '@mui/material/TextField';
import Typography from '@mui/material/Typography';
import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import api from '../../services/api';
import { approveMeetingRoomBooking, rejectMeetingRoomBooking } from '../../store/slices/meetingRoomSlice';

const statusConfig = {
  pending: { color: 'warning', label: 'Pending' },
  approved: { color: 'success', label: 'Disetujui' },
  rejected: { color: 'error', label: 'Ditolak' },
  finished: { color: 'info', label: 'Selesai' },
  cancelled: { color: 'default', label: 'Dibatalkan' },
  blocked: { color: 'secondary', label: 'Blocked' },
};

const InfoRow = ({ label, value }) => (
  <Box sx={{ py: 1.5, display: 'flex', justifyContent: 'space-between', borderBottom: '1px solid', borderColor: 'divider' }}>
    <Typography variant="body2" color="text.secondary">{label}</Typography>
    <Typography variant="body2" fontWeight={500}>{value || '-'}</Typography>
  </Box>
);

function MeetingRoomShow() {
  const { id } = useParams();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { user } = useSelector((state) => state.auth);
  const [booking, setBooking] = useState(null);
  const [loading, setLoading] = useState(true);
  const [rejectReason, setRejectReason] = useState('');
  const [actionLoading, setActionLoading] = useState(false);

  useEffect(() => {
    api.get(`/meeting-room-bookings/${id}`).then((res) => {
      setBooking(res.data.data);
      setLoading(false);
    }).catch(() => { toast.error('Gagal memuat data booking.'); setLoading(false); });
  }, [id]);

  const handleAction = async (action) => {
    setActionLoading(true);
    try {
      if (action === 'approve') await dispatch(approveMeetingRoomBooking(id)).unwrap();
      else if (action === 'reject') {
        if (!rejectReason) { toast.warning('Masukkan alasan penolakan.'); setActionLoading(false); return; }
        await dispatch(rejectMeetingRoomBooking({ id, reason: rejectReason })).unwrap();
      }
      else if (action === 'cancel') await api.post(`/meeting-room-bookings/${id}/cancel`);
      else if (action === 'finish') await api.post(`/meeting-room-bookings/${id}/finish`);
      toast.success('Aksi berhasil.');
      const res = await api.get(`/meeting-room-bookings/${id}`);
      setBooking(res.data.data);
    } catch (err) { toast.error(err || 'Gagal memproses aksi.'); }
    setActionLoading(false);
  };

  if (loading) return <Box sx={{ display: 'flex', justifyContent: 'center', py: 8 }}><CircularProgress /></Box>;
  if (!booking) return <Typography color="error" sx={{ p: 4 }}>Booking tidak ditemukan.</Typography>;

  const sc = statusConfig[booking.status] || statusConfig.pending;

  return (
    <Box>
      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 3 }}>
        <Button startIcon={<ArrowBackIcon />} onClick={() => navigate('/meeting-rooms')}>Kembali</Button>
        <Typography variant="h5" fontWeight={700}>Detail Booking #{booking.id}</Typography>
        <Chip label={sc.label} color={sc.color} sx={{ ml: 1 }} />
      </Box>

      <Grid container spacing={3}>
        <Grid item xs={12} md={8}>
          <Card>
            <CardContent sx={{ p: 3 }}>
              <Typography variant="h6" fontWeight={600} gutterBottom>Informasi Booking</Typography>
              <Divider sx={{ mb: 2 }} />
              <InfoRow label="Ruangan" value={booking.room_name} />
              <InfoRow label="Subjek" value={booking.subject} />
              <InfoRow label="Deskripsi" value={booking.description} />
              <InfoRow label="Pemohon" value={booking.user?.name} />
              <InfoRow label="Waktu Mulai" value={booking.start_datetime} />
              <InfoRow label="Waktu Selesai" value={booking.end_datetime} />
              <InfoRow label="Peserta" value={booking.attendees ? `${booking.attendees} orang` : null} />
              {booking.equipment_request && <InfoRow label="Peralatan" value={booking.equipment_request} />}
              {booking.consumption_request && <InfoRow label="Konsumsi" value={booking.consumption_request} />}
              {booking.rejection_reason && (
                <Box sx={{ mt: 2 }}>
                  <Typography variant="subtitle2" color="error.main">Alasan Penolakan:</Typography>
                  <Typography variant="body2">{booking.rejection_reason}</Typography>
                </Box>
              )}
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} md={4}>
          <Card>
            <CardContent sx={{ p: 3 }}>
              <Typography variant="h6" fontWeight={600} gutterBottom>Aksi</Typography>
              <Divider sx={{ mb: 2 }} />

              {booking.status === 'pending' && (
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 1.5 }}>
                  <Button fullWidth variant="contained" color="success" startIcon={<CheckCircleIcon />} onClick={() => handleAction('approve')} disabled={actionLoading}>
                    Setujui
                  </Button>
                  <TextField fullWidth multiline rows={2} size="small" label="Alasan Penolakan" value={rejectReason} onChange={(e) => setRejectReason(e.target.value)} />
                  <Button fullWidth variant="outlined" color="error" startIcon={<CancelIcon />} onClick={() => handleAction('reject')} disabled={actionLoading}>
                    Tolak
                  </Button>
                </Box>
              )}

              {booking.status === 'approved' && (
                <Button fullWidth variant="contained" color="info" onClick={() => handleAction('finish')} disabled={actionLoading}>
                  Selesaikan
                </Button>
              )}

              {['pending', 'approved'].includes(booking.status) && (
                <Button fullWidth variant="outlined" color="inherit" onClick={() => { if (window.confirm('Yakin batalkan booking ini?')) handleAction('cancel'); }} sx={{ mt: 1.5 }}>
                  Batalkan
                </Button>
              )}

              {!['pending', 'approved'].includes(booking.status) && (
                <Typography variant="body2" color="text.secondary" sx={{ textAlign: 'center', py: 3 }}>
                  Tidak ada aksi tersedia
                </Typography>
              )}
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Box>
  );
}

export default MeetingRoomShow;
