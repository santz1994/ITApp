import { useState } from 'react';
import Box from '@mui/material/Box';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import api from '../../services/api';

export default function ResetPassword() {
  const [email, setEmail] = useState('');
  const [status, setStatus] = useState(null);
  const [loading, setLoading] = useState(false);

  const submit = async (e) => {
    e.preventDefault(); setLoading(true); setStatus(null);
    try { await api.post('/password/email', { email }); setStatus('Permintaan reset telah dikirim ke email Anda.'); }
    catch (e) { setStatus('Gagal mengirim permintaan reset'); }
    setLoading(false);
  };

  return (
    <Box component="form" onSubmit={submit} sx={{ maxWidth: 480, mx: 'auto' }}>
      <Typography variant="h5" sx={{ mb: 2 }}>Reset Password</Typography>
      {status && <Typography sx={{ mb: 2 }}>{status}</Typography>}
      <TextField fullWidth label="Email" value={email} onChange={(e) => setEmail(e.target.value)} sx={{ mb: 2 }} />
      <Button variant="contained" type="submit" disabled={loading}>{loading ? 'Mengirim...' : 'Kirim Link Reset'}</Button>
    </Box>
  );
}
