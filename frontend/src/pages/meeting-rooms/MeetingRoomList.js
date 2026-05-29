import AddIcon from '@mui/icons-material/Add';
import BookmarkIcon from '@mui/icons-material/Bookmark';
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import CancelIcon from '@mui/icons-material/Cancel';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import MeetingRoomIcon from '@mui/icons-material/MeetingRoom';
import PendingIcon from '@mui/icons-material/Pending';
import VisibilityIcon from '@mui/icons-material/Visibility';
import Avatar from '@mui/material/Avatar';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Chip from '@mui/material/Chip';
import CircularProgress from '@mui/material/CircularProgress';
import Grid from '@mui/material/Grid';
import IconButton from '@mui/material/IconButton';
import MenuItem from '@mui/material/MenuItem';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import TextField from '@mui/material/TextField';
import Tooltip from '@mui/material/Tooltip';
import Typography from '@mui/material/Typography';
import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { fetchMeetingRoomBookings } from '../../store/slices/meetingRoomSlice';

const statusConfig = {
  pending: { color: 'warning', label: 'Pending', icon: <PendingIcon fontSize="small" /> },
  approved: { color: 'success', label: 'Disetujui', icon: <CheckCircleIcon fontSize="small" /> },
  rejected: { color: 'error', label: 'Ditolak', icon: <CancelIcon fontSize="small" /> },
  finished: { color: 'info', label: 'Selesai', icon: <CheckCircleIcon fontSize="small" /> },
  cancelled: { color: 'default', label: 'Dibatalkan', icon: <CancelIcon fontSize="small" /> },
  blocked: { color: 'secondary', label: 'Blocked', icon: <BookmarkIcon fontSize="small" /> },
};

const StatMiniCard = ({ icon, label, value, color }) => (
  <Card sx={{ height: '100%' }}>
    <CardContent sx={{ py: 2, px: 2, '&:last-child': { pb: 2 } }}>
      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
        <Avatar sx={{ bgcolor: `${color}.lighter`, color: `${color}.main`, width: 40, height: 40 }}>{icon}</Avatar>
        <Box>
          <Typography variant="h5" fontWeight={700}>{value}</Typography>
          <Typography variant="caption" color="text.secondary">{label}</Typography>
        </Box>
      </Box>
    </CardContent>
  </Card>
);

function MeetingRoomList() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { bookings, stats, loading } = useSelector((state) => state.meetingRooms);
  const [statusFilter, setStatusFilter] = useState('all');
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchMeetingRoomBookings());
  }, [dispatch]);

  const filteredBookings = bookings.filter((b) => {
    if (statusFilter !== 'all' && b.status !== statusFilter) return false;
    if (search) {
      const s = search.toLowerCase();
      return (b.subject || '').toLowerCase().includes(s) || (b.room_name || '').toLowerCase().includes(s) || (b.user?.name || '').toLowerCase().includes(s);
    }
    return true;
  });

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 3 }}>
        <Typography variant="h5" fontWeight={700}>Booking Ruang Meeting</Typography>
        <Box sx={{ display: 'flex', gap: 1 }}>
          <Button variant="outlined" startIcon={<CalendarMonthIcon />} onClick={() => navigate('/meeting-rooms/calendar')}>Kalender</Button>
          <Button variant="contained" startIcon={<AddIcon />} onClick={() => navigate('/meeting-rooms/create')}>Booking Baru</Button>
        </Box>
      </Box>

      {/* Stat Cards */}
      <Grid container spacing={2} sx={{ mb: 3 }}>
        <Grid item xs={6} sm={3}><StatMiniCard icon={<MeetingRoomIcon />} label="Total" value={stats.total || 0} color="primary" /></Grid>
        <Grid item xs={6} sm={3}><StatMiniCard icon={<PendingIcon />} label="Pending" value={stats.pending || 0} color="warning" /></Grid>
        <Grid item xs={6} sm={3}><StatMiniCard icon={<CheckCircleIcon />} label="Disetujui" value={stats.approved || 0} color="success" /></Grid>
        <Grid item xs={6} sm={3}><StatMiniCard icon={<CalendarMonthIcon />} label="Hari Ini" value={stats.today || 0} color="info" /></Grid>
      </Grid>

      {/* Filters */}
      <Card sx={{ mb: 2 }}>
        <CardContent sx={{ py: 2 }}>
          <Grid container spacing={2} alignItems="center">
            <Grid item xs={12} sm={6} md={4}>
              <TextField size="small" fullWidth placeholder="Cari subjek, ruangan, pemohon..." value={search} onChange={(e) => setSearch(e.target.value)} />
            </Grid>
            <Grid item xs={12} sm={6} md={3}>
              <TextField size="small" select fullWidth label="Status" value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)}>
                <MenuItem value="all">Semua Status</MenuItem>
                {Object.entries(statusConfig).map(([key, val]) => (
                  <MenuItem key={key} value={key}>{val.label}</MenuItem>
                ))}
              </TextField>
            </Grid>
          </Grid>
        </CardContent>
      </Card>

      {/* Table */}
      <Card>
        <TableContainer>
          {loading ? (
            <Box sx={{ display: 'flex', justifyContent: 'center', py: 8 }}><CircularProgress /></Box>
          ) : (
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>Ruangan</TableCell>
                  <TableCell>Subjek</TableCell>
                  <TableCell>Pemohon</TableCell>
                  <TableCell>Waktu</TableCell>
                  <TableCell>Status</TableCell>
                  <TableCell align="right">Aksi</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {filteredBookings.map((booking) => {
                  const sc = statusConfig[booking.status] || statusConfig.pending;
                  return (
                    <TableRow key={booking.id} hover sx={{ cursor: 'pointer' }} onClick={() => navigate(`/meeting-rooms/${booking.id}`)}>
                      <TableCell>
                        <Typography variant="body2" fontWeight={600}>{booking.room_name || '-'}</Typography>
                      </TableCell>
                      <TableCell>
                        <Typography variant="body2">{booking.subject || '-'}</Typography>
                      </TableCell>
                      <TableCell>
                        <Typography variant="body2">{booking.user?.name || '-'}</Typography>
                      </TableCell>
                      <TableCell>
                        <Typography variant="body2">{booking.start_datetime}</Typography>
                        <Typography variant="caption" color="text.secondary">s/d {booking.end_datetime}</Typography>
                      </TableCell>
                      <TableCell>
                        <Chip icon={sc.icon} label={sc.label} color={sc.color} size="small" variant="outlined" />
                      </TableCell>
                      <TableCell align="right">
                        <Tooltip title="Detail">
                          <IconButton size="small" onClick={(e) => { e.stopPropagation(); navigate(`/meeting-rooms/${booking.id}`); }}>
                            <VisibilityIcon fontSize="small" />
                          </IconButton>
                        </Tooltip>
                      </TableCell>
                    </TableRow>
                  );
                })}
                {filteredBookings.length === 0 && (
                  <TableRow>
                    <TableCell colSpan={6} align="center" sx={{ py: 6 }}>
                      <MeetingRoomIcon sx={{ fontSize: 48, color: 'text.disabled', mb: 1 }} />
                      <Typography color="text.secondary">Belum ada booking ruang meeting.</Typography>
                      <Button sx={{ mt: 1 }} size="small" onClick={() => navigate('/meeting-rooms/create')}>Buat Booking</Button>
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          )}
        </TableContainer>
      </Card>
    </Box>
  );
}

export default MeetingRoomList;
