import interactionPlugin from '@fullcalendar/interaction';
import FullCalendar from '@fullcalendar/react';
import resourceTimelinePlugin from '@fullcalendar/resource-timeline';
import timeGridPlugin from '@fullcalendar/timegrid';
import AddIcon from '@mui/icons-material/Add';
import BlockIcon from '@mui/icons-material/Block';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Chip from '@mui/material/Chip';
import CircularProgress from '@mui/material/CircularProgress';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogTitle from '@mui/material/DialogTitle';
import MenuItem from '@mui/material/MenuItem';
import TextField from '@mui/material/TextField';
import Typography from '@mui/material/Typography';
import dayjs from 'dayjs';
import { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import api from '../../services/api';

const STATUS_COLORS = {
    pending: '#ed6c02',
    approved: '#2e7d32',
    rejected: '#d32f2f',
    finished: '#0288d1',
    cancelled: '#757575',
    blocked: '#9c27b0',
};

const ROOMS = ['Ruang Meeting 1', 'Ruang Meeting 2', 'Ruang Meeting 3'];
const now = dayjs();

export default function ReceptionistSchedule() {
    const navigate = useNavigate();
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [today, setToday] = useState(now.format('YYYY-MM-DD'));

    // Quick booking dialog
    const [quickOpen, setQuickOpen] = useState(false);
    const [quickForm, setQuickForm] = useState({
        room_name: ROOMS[0],
        subject: '',
        start_datetime: now.hour(9).minute(0).format('YYYY-MM-DDTHH:mm'),
        end_datetime: now.hour(10).minute(0).format('YYYY-MM-DDTHH:mm'),
        description: '',
    });

    // Block room dialog
    const [blockOpen, setBlockOpen] = useState(false);
    const [blockForm, setBlockForm] = useState({
        room_name: ROOMS[0],
        subject: 'BLOCKED - Urgent Meeting',
        start_datetime: now.hour(9).minute(0).format('YYYY-MM-DDTHH:mm'),
        end_datetime: now.hour(10).minute(0).format('YYYY-MM-DDTHH:mm'),
        description: '',
    });

    const fetchBookings = useCallback(async () => {
        try {
            const res = await api.get('/meeting-room-bookings', { params: { date: today, per_page: 200 } });
            const data = res.data.data || [];
            setEvents(data.map(b => ({
                id: b.id,
                resourceId: b.room_name,
                title: b.subject || '-',
                start: b.start_datetime,
                end: b.end_datetime,
                color: STATUS_COLORS[b.status] || '#757575',
                borderColor: STATUS_COLORS[b.status] || '#757575',
                textColor: '#fff',
                extendedProps: { status: b.status, user: b.user?.name, booking: b },
            })));
        } catch (err) {
            console.error('Failed to load bookings', err);
        }
        setLoading(false);
    }, [today]);

    useEffect(() => { fetchBookings(); }, [fetchBookings]);

    // Drag-drop handler
    const handleEventDrop = async (info) => {
        try {
            await api.put(`/meeting-room-bookings/${info.event.id}`, {
                start_datetime: info.event.startStr,
                end_datetime: info.event.endStr,
                room_name: info.event.getResources()[0]?.id || info.event.extendedProps.booking.room_name,
            });
            toast.success('Booking moved successfully');
        } catch (err) {
            toast.error('Failed to move booking');
            info.revert();
        }
    };

    // Quick booking submit
    const handleQuickBook = async () => {
        if (!quickForm.subject) { toast.warning('Subject is required'); return; }
        try {
            await api.post('/meeting-room-bookings', { ...quickForm, is_blocking: false });
            toast.success('Quick booking created');
            setQuickOpen(false);
            setQuickForm(f => ({ ...f, subject: '', description: '' }));
            fetchBookings();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to create booking');
        }
    };

    // Block room submit
    const handleBlock = async () => {
        try {
            await api.post('/meeting-room-bookings', { ...blockForm, is_blocking: true });
            toast.success('Room blocked');
            setBlockOpen(false);
            fetchBookings();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to block room');
        }
    };

    const handleEventClick = (info) => {
        navigate(`/meeting-rooms/${info.event.id}`);
    };

    const resources = ROOMS.map(name => ({ id: name, title: name }));

    if (loading) return <Box sx={{ display: 'flex', justifyContent: 'center', py: 8 }}><CircularProgress /></Box>;

    return (
        <Box>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 3, flexWrap: 'wrap', gap: 1 }}>
                <Box>
                    <Typography variant="h5" fontWeight={700}>Receptionist Schedule</Typography>
                    <Typography variant="body2" color="text.secondary">{dayjs(today).format('dddd, DD MMMM YYYY')}</Typography>
                </Box>
                <Box sx={{ display: 'flex', gap: 1 }}>
                    <TextField
                        type="date" size="small" value={today}
                        onChange={(e) => setToday(e.target.value)}
                        sx={{ minWidth: 160 }}
                    />
                    <Button variant="contained" startIcon={<AddIcon />} onClick={() => setQuickOpen(true)}>
                        Quick Booking
                    </Button>
                    <Button variant="outlined" color="secondary" startIcon={<BlockIcon />} onClick={() => setBlockOpen(true)}>
                        Block Room
                    </Button>
                </Box>
            </Box>

            {/* Status Legend */}
            <Box sx={{ display: 'flex', gap: 1, mb: 2, flexWrap: 'wrap' }}>
                {Object.entries(STATUS_COLORS).map(([st, color]) => (
                    <Chip key={st} label={st.charAt(0).toUpperCase() + st.slice(1)} size="small"
                        sx={{ bgcolor: color, color: '#fff', fontWeight: 600 }} />
                ))}
            </Box>

            <Card elevation={1} sx={{ borderRadius: 2 }}>
                <CardContent sx={{ p: { xs: 1, sm: 2 }, '& .fc': { fontSize: '0.85rem' } }}>
                    <FullCalendar
                        plugins={[resourceTimelinePlugin, timeGridPlugin, interactionPlugin]}
                        initialView="resourceTimelineDay"
                        resources={resources}
                        resourceAreaHeaderContent="Room"
                        events={events}
                        editable={true}
                        eventDrop={handleEventDrop}
                        eventClick={handleEventClick}
                        height={600}
                        slotMinTime="07:00:00"
                        slotMaxTime="21:00:00"
                        slotDuration="00:30:00"
                        nowIndicator={true}
                        headerToolbar={false}
                        date={today}
                        dayHeaders={false}
                        resourceAreaWidth={160}
                        eventMinHeight={30}
                        eventDisplay="block"
                        eventContent={(arg) => (
                            <Box sx={{ px: 0.5, overflow: 'hidden' }}>
                                <Typography variant="caption" fontWeight={700} noWrap sx={{ color: '#fff', fontSize: 11 }}>
                                    {arg.event.title}
                                </Typography>
                                <Typography variant="caption" noWrap sx={{ color: 'rgba(255,255,255,0.8)', fontSize: 10, display: 'block' }}>
                                    {arg.event.extendedProps.user || ''}
                                </Typography>
                            </Box>
                        )}
                    />
                </CardContent>
            </Card>

            {/* Quick Booking Dialog */}
            <Dialog open={quickOpen} onClose={() => setQuickOpen(false)} maxWidth="sm" fullWidth>
                <DialogTitle>Quick Booking</DialogTitle>
                <DialogContent sx={{ pt: '16px !important' }}>
                    <TextField fullWidth select label="Room" value={quickForm.room_name} sx={{ mb: 2 }}
                        onChange={(e) => setQuickForm(f => ({ ...f, room_name: e.target.value }))}>
                        {ROOMS.map(r => <MenuItem key={r} value={r}>{r}</MenuItem>)}
                    </TextField>
                    <TextField fullWidth required label="Subject" value={quickForm.subject} sx={{ mb: 2 }}
                        onChange={(e) => setQuickForm(f => ({ ...f, subject: e.target.value }))} />
                    <TextField fullWidth type="datetime-local" label="Start" value={quickForm.start_datetime} sx={{ mb: 2 }}
                        onChange={(e) => setQuickForm(f => ({ ...f, start_datetime: e.target.value }))} InputLabelProps={{ shrink: true }} />
                    <TextField fullWidth type="datetime-local" label="End" value={quickForm.end_datetime} sx={{ mb: 2 }}
                        onChange={(e) => setQuickForm(f => ({ ...f, end_datetime: e.target.value }))} InputLabelProps={{ shrink: true }} />
                    <TextField fullWidth label="Description" multiline rows={2} value={quickForm.description}
                        onChange={(e) => setQuickForm(f => ({ ...f, description: e.target.value }))} />
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setQuickOpen(false)}>Cancel</Button>
                    <Button variant="contained" onClick={handleQuickBook}>Book Now</Button>
                </DialogActions>
            </Dialog>

            {/* Block Room Dialog */}
            <Dialog open={blockOpen} onClose={() => setBlockOpen(false)} maxWidth="sm" fullWidth>
                <DialogTitle>Block Meeting Room</DialogTitle>
                <DialogContent sx={{ pt: '16px !important' }}>
                    <TextField fullWidth select label="Room" value={blockForm.room_name} sx={{ mb: 2 }}
                        onChange={(e) => setBlockForm(f => ({ ...f, room_name: e.target.value }))}>
                        {ROOMS.map(r => <MenuItem key={r} value={r}>{r}</MenuItem>)}
                    </TextField>
                    <TextField fullWidth label="Reason" value={blockForm.subject} sx={{ mb: 2 }}
                        onChange={(e) => setBlockForm(f => ({ ...f, subject: e.target.value }))} />
                    <TextField fullWidth type="datetime-local" label="From" value={blockForm.start_datetime} sx={{ mb: 2 }}
                        onChange={(e) => setBlockForm(f => ({ ...f, start_datetime: e.target.value }))} InputLabelProps={{ shrink: true }} />
                    <TextField fullWidth type="datetime-local" label="Until" value={blockForm.end_datetime} sx={{ mb: 2 }}
                        onChange={(e) => setBlockForm(f => ({ ...f, end_datetime: e.target.value }))} InputLabelProps={{ shrink: true }} />
                    <TextField fullWidth label="Notes" multiline rows={2} value={blockForm.description}
                        onChange={(e) => setBlockForm(f => ({ ...f, description: e.target.value }))} />
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setBlockOpen(false)}>Cancel</Button>
                    <Button variant="contained" color="secondary" onClick={handleBlock}>Block Room</Button>
                </DialogActions>
            </Dialog>
        </Box>
    );
}
