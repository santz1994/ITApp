import DeleteIcon from '@mui/icons-material/Delete';
import EditIcon from '@mui/icons-material/Edit';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import IconButton from '@mui/material/IconButton';
import Typography from '@mui/material/Typography';
import { useEffect, useState } from 'react';
import api from '../../api/client';
import InventoryForm from './InventoryForm';

export default function InventoryList() {
    const [requests, setRequests] = useState([]);
    const [openForm, setOpenForm] = useState(false);
    const [editing, setEditing] = useState(null);

    useEffect(() => { fetchRequests(); }, []);

    async function fetchRequests() {
        try {
            const res = await api.get('/api/inventory-requests');
            setRequests(res.data || []);
        } catch (e) { console.error(e); }
    }

    return (
        <Box>
            <Typography variant="h5" mb={2}>Inventory Requests</Typography>
            <Box sx={{ display: 'flex', gap: 1, mb: 2 }}>
                <Button variant="contained" onClick={() => { setEditing(null); setOpenForm(true); }}>New Request</Button>
            </Box>
            <div>
                {requests.length === 0 && <Typography>No requests yet.</Typography>}
                {requests.map(r => (
                    <Box key={r.id} sx={{ p: 1, border: '1px solid #eee', mb: 1, display: 'flex', justifyContent: 'space-between' }}>
                        <Box>
                            <Typography>{r.item || 'No item'}</Typography>
                            <Typography variant="caption">Qty: {r.quantity}</Typography>
                        </Box>
                        <Box>
                            <IconButton size="small" onClick={() => { setEditing(r); setOpenForm(true); }}><EditIcon fontSize="small" /></IconButton>
                            <IconButton size="small" onClick={async () => { if (!confirm('Delete?')) return; try { await api.delete(`/api/inventory-requests/${r.id}`); fetchRequests(); } catch (e) { alert('Delete failed') } }}><DeleteIcon fontSize="small" /></IconButton>
                        </Box>
                    </Box>
                ))}
            </div>
            <InventoryForm open={openForm} request={editing} onClose={() => { setOpenForm(false); setEditing(null); }} onCreated={fetchRequests} onUpdated={fetchRequests} />
        </Box>
    );
}
