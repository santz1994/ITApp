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
import { useEffect, useState } from 'react';
import api from '../../api/client';

export default function InventoryForm({ open, onClose, onCreated, onUpdated, request }) {
  const [item, setItem] = useState('');
  const [quantity, setQuantity] = useState(1);
  const [category, setCategory] = useState('');
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    let mounted = true;
    (async () => {
      try {
        const res = await api.get('/api/inventory-categories');
        if (mounted && Array.isArray(res.data)) setCategories(res.data);
      } catch (e) {}
    })();
    return () => { mounted = false; };
  }, []);

  useEffect(() => {
    if (request) {
      setItem(request.item || '');
      setQuantity(request.quantity || 1);
      setCategory(request.category_id || request.category || '');
    }
  }, [request]);

  async function handleSubmit(e) {
    e.preventDefault();
    setError(null);
    if (!item) return setError('Item is required');
    if (quantity <= 0) return setError('Quantity must be positive');
    setLoading(true);
    try {
      const payload = { item, quantity, category_id: category || null };
      if (request && request.id) {
        await api.put(`/api/inventory-requests/${request.id}`, payload);
        if (onUpdated) onUpdated();
      } else {
        await api.post('/api/inventory-requests', payload);
        if (onCreated) onCreated();
      }
      setLoading(false);
      handleClose();
    } catch (err) {
      setLoading(false);
      setError(err.response?.data?.message || 'Failed to save');
    }
  }

  function handleClose() {
    setItem(''); setQuantity(1); setCategory(''); setError(null);
    if (onClose) onClose();
  }

  return (
    <Dialog open={open} onClose={handleClose} fullWidth maxWidth="sm">
      <DialogTitle>Inventory Request</DialogTitle>
      <form onSubmit={handleSubmit}>
        <DialogContent>
          <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
            <TextField label="Item" value={item} onChange={(e)=>setItem(e.target.value)} fullWidth />
            <TextField label="Quantity" type="number" value={quantity} onChange={(e)=>setQuantity(Number(e.target.value))} fullWidth />
            <FormControl fullWidth>
              <InputLabel id="cat-label">Category</InputLabel>
              <Select labelId="cat-label" label="Category" value={category} onChange={(e)=>setCategory(e.target.value)}>
                <MenuItem value="">None</MenuItem>
                {categories.map(c => <MenuItem key={c.id} value={c.id}>{c.name}</MenuItem>)}
              </Select>
            </FormControl>
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
