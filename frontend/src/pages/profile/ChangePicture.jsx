import { useState } from 'react';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import Avatar from '@mui/material/Avatar';
import { profileApi } from '../../services/api';

export default function ChangePicture() {
  const [file, setFile] = useState(null);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState(null);

  const handleFile = (e) => setFile(e.target.files[0]);

  const handleUpload = async () => {
    if (!file) return setMessage('Pilih file terlebih dahulu');
    const fd = new FormData(); fd.append('picture', file);
    setLoading(true);
    try { await profileApi.uploadPicture(fd); setMessage('Berhasil mengunggah'); }
    catch (e) { setMessage(e.response?.data?.message || 'Gagal mengunggah'); }
    setLoading(false);
  };

  return (
    <Box>
      <Typography variant="h5">Ganti Foto Profil</Typography>
      <Box sx={{ display: 'flex', alignItems: 'center', gap: 2, mt: 2 }}>
        <Avatar sx={{ width: 80, height: 80 }} />
        <input type="file" accept="image/*" onChange={handleFile} />
      </Box>
      {message && <Typography sx={{ mt: 2 }}>{message}</Typography>}
      <Box sx={{ mt: 2 }}>
        <Button variant="contained" onClick={handleUpload} disabled={loading}>{loading ? 'Mengunggah...' : 'Unggah'}</Button>
      </Box>
    </Box>
  );
}
