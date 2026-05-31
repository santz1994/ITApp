import AddIcon from '@mui/icons-material/Add';
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import VisibilityIcon from '@mui/icons-material/Visibility';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Chip from '@mui/material/Chip';
import CircularProgress from '@mui/material/CircularProgress';
import IconButton from '@mui/material/IconButton';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TablePagination from '@mui/material/TablePagination';
import TableRow from '@mui/material/TableRow';
import Typography from '@mui/material/Typography';
import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Link, useNavigate } from 'react-router-dom';
import { fetchMeetingRoomBookings } from '../../store/slices/meetingRoomSlice';

const STATUS_MAP = {
  pending: { label: 'Pending', color: 'warning' },
  approved: { label: 'Approved', color: 'success' },
  rejected: { label: 'Rejected', color: 'error' },
  cancelled: { label: 'Cancelled', color: 'default' },
  finished: { label: 'Finished', color: 'info' },
};

export default function MeetingRoomsList() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { bookings, loading } = useSelector((s) => s.meetingRooms);
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(15);

  useEffect(() => { dispatch(fetchMeetingRoomBookings()); }, [dispatch]);

  const list = Array.isArray(bookings) ? bookings : bookings?.data || [];

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 3 }}>
        <Typography variant="h5" fontWeight={700}>Meeting Room Bookings</Typography>
        <Box sx={{ display: 'flex', gap: 1 }}>
          <Button component={Link} to="/meeting-rooms/calendar" variant="outlined" startIcon={<CalendarMonthIcon />}>
            Calendar
          </Button>
          <Button component={Link} to="/meeting-rooms/create" variant="contained" startIcon={<AddIcon />}>
            New Booking
          </Button>
        </Box>
      </Box>

      <Card elevation={1} sx={{ borderRadius: 2 }}>
        <CardContent sx={{ p: 0 }}>
          {loading ? (
            <Box sx={{ display: 'flex', justifyContent: 'center', py: 8 }}><CircularProgress /></Box>
          ) : list.length === 0 ? (
            <Box sx={{ py: 8, textAlign: 'center' }}>
              <Typography color="text.secondary">No bookings found. Create a new booking to get started.</Typography>
            </Box>
          ) : (
            <>
              <TableContainer>
                <Table size="small">
                  <TableHead>
                    <TableRow sx={{ bgcolor: 'grey.50' }}>
                      <TableCell sx={{ fontWeight: 700 }}>Room</TableCell>
                      <TableCell sx={{ fontWeight: 700 }}>Subject</TableCell>
                      <TableCell sx={{ fontWeight: 700 }}>Date & Time</TableCell>
                      <TableCell sx={{ fontWeight: 700 }}>Booked By</TableCell>
                      <TableCell sx={{ fontWeight: 700 }}>Status</TableCell>
                      <TableCell sx={{ fontWeight: 700 }} align="right">Action</TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {list.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage).map((b) => {
                      const st = STATUS_MAP[b.status] || { label: b.status, color: 'default' };
                      return (
                        <TableRow key={b.id} hover>
                          <TableCell>{b.room_name || '-'}</TableCell>
                          <TableCell>{b.subject || '-'}</TableCell>
                          <TableCell>
                            <Typography variant="body2">
                              {b.start_datetime ? new Date(b.start_datetime).toLocaleDateString('id-ID') : '-'}
                            </Typography>
                            <Typography variant="caption" color="text.secondary">
                              {b.start_datetime ? new Date(b.start_datetime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : ''}
                              {b.end_datetime ? ` - ${new Date(b.end_datetime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}` : ''}
                            </Typography>
                          </TableCell>
                          <TableCell>{b.user?.name || '-'}</TableCell>
                          <TableCell><Chip label={st.label} size="small" color={st.color} variant="outlined" /></TableCell>
                          <TableCell align="right">
                            <IconButton size="small" onClick={() => navigate(`/meeting-rooms/${b.id}`)}>
                              <VisibilityIcon fontSize="small" />
                            </IconButton>
                          </TableCell>
                        </TableRow>
                      );
                    })}
                  </TableBody>
                </Table>
              </TableContainer>
              <TablePagination
                component="div" count={list.length}
                page={page} onPageChange={(e, p) => setPage(p)}
                rowsPerPage={rowsPerPage} onRowsPerPageChange={(e) => { setRowsPerPage(parseInt(e.target.value)); setPage(0); }}
                rowsPerPageOptions={[10, 15, 25, 50]}
              />
            </>
          )}
        </CardContent>
      </Card>
    </Box>
  );
}
<Box key={b.id} sx={{ p: 1, border: '1px solid #eee', mb: 1, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
  <Box>
    <Typography>{b.subject || 'No subject'}</Typography>
    <Typography variant="caption">{b.start_time} - {b.end_time}</Typography>
  </Box>
  <Box>
    <IconButton aria-label="edit" size="small" onClick={() => { setEditing(b); setOpenForm(true); }}>
      <EditIcon fontSize="small" />
    </IconButton>
    <IconButton aria-label="delete" size="small" onClick={async () => {
      if (!confirm('Delete this booking?')) return;
      try { await api.delete(`/api/meeting-room-bookings/${b.id}`); fetchBookings(); } catch (e) { console.error(e); alert('Delete failed'); }
    }}>
      <DeleteIcon fontSize="small" />
    </IconButton>
  </Box>
</Box>
        ))}
      </div >
      <MeetingRoomForm open={openForm} onClose={() => setOpenForm(false)} onCreated={fetchBookings} />
      <MeetingRoomForm open={openForm} booking={editing} onClose={() => { setOpenForm(false); setEditing(null); }} onCreated={fetchBookings} onUpdated={fetchBookings} />
    </Box >
  );
}
