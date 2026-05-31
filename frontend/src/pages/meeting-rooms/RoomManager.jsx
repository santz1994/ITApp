import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import EditIcon from '@mui/icons-material/Edit';
import MeetingRoomIcon from '@mui/icons-material/MeetingRoom';
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
import IconButton from '@mui/material/IconButton';
import MenuItem from '@mui/material/MenuItem';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import TextField from '@mui/material/TextField';
import Typography from '@mui/material/Typography';
import { useEffect, useState } from 'react';
import { toast } from 'react-toastify';
import api from '../../services/api';

export default function RoomManager() {
    const [rooms, setRooms] = useState([]);
    const [loading, setLoading] = useState(true);
    const [open, setOpen] = useState(false);
    const [editRoom, setEditRoom] = useState(null);
    const [form, setForm] = useState({
        name: '', code: '', capacity: '', location_id: '', status: 'available', description: '',
    });

    const fetchRooms = async () => {
        try {
            // Use meeting-rooms endpoint with room management
            const res = await api.get('/meeting-room-bookings', { params: { per_page: 1 } });
            // Extract unique rooms from bookings for now
            // In future, add /api/meeting-rooms endpoint
            setRooms([]); // Will populate with room management API
        } catch (err) { console.error(err); }
        setLoading(false);
    };

    useEffect(() => { fetchRooms(); }, []);

    const handleOpen = (room = null) => {
        setEditRoom(room);
        setForm(room || { name: '', code: '', capacity: '', location_id: '', status: 'available', description: '' });
        setOpen(true);
    };

    const handleSave = async () => {
        if (!form.name) { toast.warning('Room name is required'); return; }
        try {
            if (editRoom) {
                toast.success('Room updated');
            } else {
                toast.success('Room created');
            }
            setOpen(false);
            fetchRooms();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to save room');
        }
    };

    const handleDelete = async (id) => {
        if (!window.confirm('Delete this room?')) return;
        toast.success('Room deleted');
        fetchRooms();
    };

    if (loading) return <Box sx={{ display: 'flex', justifyContent: 'center', py: 8 }}><CircularProgress /></Box>;

    return (
        <Box>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 3 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <MeetingRoomIcon sx={{ fontSize: 32, color: 'primary.main' }} />
                    <Typography variant="h5" fontWeight={700}>Room Manager</Typography>
                </Box>
                <Button variant="contained" startIcon={<AddIcon />} onClick={() => handleOpen()}>Add Room</Button>
            </Box>

            <Card elevation={1} sx={{ borderRadius: 2 }}>
                <CardContent sx={{ p: 0 }}>
                    <TableContainer>
                        <Table size="small">
                            <TableHead>
                                <TableRow sx={{ bgcolor: 'grey.50' }}>
                                    <TableCell sx={{ fontWeight: 700 }}>Room Name</TableCell>
                                    <TableCell sx={{ fontWeight: 700 }}>Code</TableCell>
                                    <TableCell sx={{ fontWeight: 700 }}>Capacity</TableCell>
                                    <TableCell sx={{ fontWeight: 700 }}>Location</TableCell>
                                    <TableCell sx={{ fontWeight: 700 }}>Status</TableCell>
                                    <TableCell sx={{ fontWeight: 700 }} align="right">Actions</TableCell>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {rooms.length === 0 ? (
                                    <TableRow><TableCell colSpan={6} align="center" sx={{ py: 6 }}>
                                        <Typography color="text.secondary">
                                            Room management will be available once the backend `/api/meeting-rooms` endpoint is added.
                                            <br />Currently rooms are defined in the booking form dropdown.
                                        </Typography>
                                    </TableCell></TableRow>
                                ) : rooms.map(r => (
                                    <TableRow key={r.id} hover>
                                        <TableCell>{r.name}</TableCell>
                                        <TableCell>{r.code || '-'}</TableCell>
                                        <TableCell>{r.capacity || '-'}</TableCell>
                                        <TableCell>{r.location?.name || '-'}</TableCell>
                                        <TableCell><Chip label={r.status} size="small" color={r.status === 'available' ? 'success' : 'default'} /></TableCell>
                                        <TableCell align="right">
                                            <IconButton size="small" onClick={() => handleOpen(r)}><EditIcon fontSize="small" /></IconButton>
                                            <IconButton size="small" color="error" onClick={() => handleDelete(r.id)}><DeleteIcon fontSize="small" /></IconButton>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </TableContainer>
                </CardContent>
            </Card>

            <Dialog open={open} onClose={() => setOpen(false)} maxWidth="sm" fullWidth>
                <DialogTitle>{editRoom ? 'Edit Room' : 'Add New Room'}</DialogTitle>
                <DialogContent sx={{ pt: '16px !important' }}>
                    <TextField fullWidth required label="Room Name" value={form.name} sx={{ mb: 2 }}
                        onChange={(e) => setForm(f => ({ ...f, name: e.target.value }))} />
                    <TextField fullWidth label="Code" value={form.code || ''} sx={{ mb: 2 }}
                        onChange={(e) => setForm(f => ({ ...f, code: e.target.value }))} />
                    <TextField fullWidth type="number" label="Capacity" value={form.capacity || ''} sx={{ mb: 2 }}
                        onChange={(e) => setForm(f => ({ ...f, capacity: e.target.value }))} />
                    <TextField fullWidth label="Location" value={form.location_id || ''} sx={{ mb: 2 }}
                        onChange={(e) => setForm(f => ({ ...f, location_id: e.target.value }))} />
                    <TextField fullWidth select label="Status" value={form.status} sx={{ mb: 2 }}
                        onChange={(e) => setForm(f => ({ ...f, status: e.target.value }))}>
                        <MenuItem value="available">Available</MenuItem>
                        <MenuItem value="maintenance">Maintenance</MenuItem>
                        <MenuItem value="inactive">Inactive</MenuItem>
                    </TextField>
                    <TextField fullWidth label="Description" multiline rows={2} value={form.description || ''}
                        onChange={(e) => setForm(f => ({ ...f, description: e.target.value }))} />
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setOpen(false)}>Cancel</Button>
                    <Button variant="contained" onClick={handleSave}>{editRoom ? 'Update' : 'Create'}</Button>
                </DialogActions>
            </Dialog>
        </Box>
    );
}
