import { useState } from 'react';
import Box from '@mui/material/Box';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import api from '../../services/api';
import { useNavigate } from 'react-router-dom';

export default function Register() {
  const [form, setForm] = useState({ name: '', email: '', password: '', password_confirmation: '' });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

  const submit = async (e) => {
    e.preventDefault(); setLoading(true); setError(null);
    try { await api.post('/register', form); navigate('/login'); }
    catch (e) { setError(e.response?.data?.message || 'Gagal mendaftar'); }
    setLoading(false);
  };

  return (
    <Box component="form" onSubmit={submit} sx={{ maxWidth: 480, mx: 'auto' }}>
      <Typography variant="h5" sx={{ mb: 2 }}>Daftar</Typography>
      {error && <Typography color="error">{error}</Typography>}
      <TextField fullWidth label="Nama" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} sx={{ mb: 2 }} />
      <TextField fullWidth label="Email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} sx={{ mb: 2 }} />
      <TextField fullWidth label="Password" type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} sx={{ mb: 2 }} />
      <TextField fullWidth label="Konfirmasi Password" type="password" value={form.password_confirmation} onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })} sx={{ mb: 2 }} />
      <Button variant="contained" type="submit" disabled={loading}>{loading ? 'Mendaftar...' : 'Daftar'}</Button>
    </Box>
  );
}
