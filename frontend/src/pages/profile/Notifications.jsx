import { useEffect, useState } from 'react';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import FormControlLabel from '@mui/material/FormControlLabel';
import Switch from '@mui/material/Switch';
import Button from '@mui/material/Button';
import { profileApi } from '../../services/api';

export default function Notifications() {
  const [settings, setSettings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    profileApi.getNotifications().then((res) => { setSettings(res.data.data || res.data); setLoading(false); }).catch(() => setLoading(false));
  }, []);

  const toggle = (i) => {
    const copy = [...settings]; copy[i].enabled = !copy[i].enabled; setSettings(copy);
  };

  const save = async () => {
    setSaving(true);
    try { await profileApi.updateNotifications({ notifications: settings }); }
    catch (e) { /* ignore for now */ }
    setSaving(false);
  };

  if (loading) return <Typography>Memuat...</Typography>;

  return (
    <Box>
      <Typography variant="h5">Preferensi Notifikasi</Typography>
      <Box sx={{ mt: 2 }}>
        {settings.map((s, i) => (
          <FormControlLabel key={s.key || i} control={<Switch checked={!!s.enabled} onChange={() => toggle(i)} />} label={s.label || s.name || s.key} />
        ))}
      </Box>
      <Box sx={{ mt: 2 }}>
        <Button variant="contained" onClick={save} disabled={saving}>{saving ? 'Menyimpan...' : 'Simpan'}</Button>
      </Box>
    </Box>
  );
}
