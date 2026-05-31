import CheckIcon from '@mui/icons-material/Check';
import CloseIcon from '@mui/icons-material/Close';
import Box from '@mui/material/Box';
import IconButton from '@mui/material/IconButton';
import Typography from '@mui/material/Typography';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import { useEffect, useState } from 'react';
import api from '../../api/client';

export default function ApprovalsList() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(false);
  const [selected, setSelected] = useState(null);
  const [notes, setNotes] = useState('');

  useEffect(() => { fetchItems(); }, []);

  async function fetchItems() {
    setLoading(true);
    try {
      const res = await api.get('/api/meeting-room-bookings', { params: { status: 'pending' } });
      setItems(res.data || []);
    } catch (e) { console.error(e); }
    setLoading(false);
  }

  async function doAction(id, action, payload = {}) {
    if (action === 'reject' && (!payload.director_notes || payload.director_notes.length < 10)) {
      alert('Please provide rejection notes (at least 10 characters).');
      return;
    }
    if (!confirm(`Are you sure you want to ${action} this item?`)) return;
    try {
      await api.post(`/api/meeting-room-bookings/${id}/${action}`, payload);
      setSelected(null);
      setNotes('');
      fetchItems();
    } catch (e) { alert('Action failed'); }
  }

  async function openDetail(id) {
    setLoading(true);
    try {
      const res = await api.get(`/api/meeting-room-bookings/${id}`);
      setSelected(res.data || null);
    } catch (e) { console.error(e); alert('Failed to load details'); }
    setLoading(false);
  }

  return (
    <Box>
      <Typography variant="h5" mb={2}>Pending Approvals</Typography>
      {loading && <Typography>Loading…</Typography>}
      <div>
        {items.length === 0 && !loading && <Typography>No pending approvals.</Typography>}
        {items.map(it => (
          <Box key={it.id} sx={{ p:1, border: '1px solid #eee', mb:1, display: 'flex', justifyContent: 'space-between', cursor: 'pointer' }}>
            <Box onClick={() => openDetail(it.id)}>
              <Typography>{it.subject || it.purpose || `Request #${it.id}`}</Typography>
              <Typography variant="caption">{it.requester_name || it.user?.name || ''}</Typography>
            </Box>
            <Box>
              <IconButton size="small" color="success" onClick={()=>doAction(it.id, 'approve')}><CheckIcon fontSize="small"/></IconButton>
              <IconButton size="small" color="error" onClick={()=>{ setSelected(it); setNotes(''); }}><CloseIcon fontSize="small"/></IconButton>
            </Box>
          </Box>
        ))}
      </div>

      <Dialog open={!!selected} onClose={() => setSelected(null)} maxWidth="sm" fullWidth>
        <DialogTitle>Approval</DialogTitle>
        <DialogContent>
          {selected && (
            <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
              <Typography variant="subtitle1">{selected.subject || selected.purpose}</Typography>
              <Typography variant="caption">Requested by: {selected.requester_name || selected.user?.name}</Typography>
              <Typography>Room: {selected.room_name || selected.room}</Typography>
              <Typography>Time: {selected.start_time || selected.start_datetime} - {selected.end_time || selected.end_datetime}</Typography>
              <TextField label="Director notes / Reason" multiline minRows={3} value={notes} onChange={(e)=>setNotes(e.target.value)} fullWidth />
            </Box>
          )}
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setSelected(null)}>Close</Button>
          <Button color="error" onClick={() => doAction(selected.id, 'reject', { director_notes: notes })}>Reject</Button>
          <Button variant="contained" onClick={() => doAction(selected.id, 'approve', { director_notes: notes })}>Approve</Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
