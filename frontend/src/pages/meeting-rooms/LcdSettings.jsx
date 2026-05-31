import MonitorIcon from '@mui/icons-material/Monitor';
import SaveIcon from '@mui/icons-material/Save';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Checkbox from '@mui/material/Checkbox';
import Divider from '@mui/material/Divider';
import FormControlLabel from '@mui/material/FormControlLabel';
import TextField from '@mui/material/TextField';
import Typography from '@mui/material/Typography';
import { useState } from 'react';
import { toast } from 'react-toastify';

const ROOMS = ['Ruang Meeting 1', 'Ruang Meeting 2', 'Ruang Meeting 3'];

export default function LcdSettings() {
  const [settings, setSettings] = useState({
    enabled_rooms: [...ROOMS],
    refresh_interval: 60,
    show_company_logo: true,
    company_name: 'PT Quty Karunia',
    header_text: 'Meeting Room Schedule',
    show_status_badge: true,
    timezone: 'Asia/Jakarta',
  });

  const handleToggleRoom = (room) => {
    setSettings(s => ({
      ...s,
      enabled_rooms: s.enabled_rooms.includes(room)
        ? s.enabled_rooms.filter(r => r !== room)
        : [...s.enabled_rooms, room],
    }));
  };

  const handleSave = () => {
    localStorage.setItem('lcd_settings', JSON.stringify(settings));
    toast.success('LCD settings saved');
  };

  return (
    <Box>
      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 3 }}>
        <MonitorIcon sx={{ fontSize: 32, color: 'primary.main' }} />
        <Typography variant="h5" fontWeight={700}>LCD Display Settings</Typography>
      </Box>

      <Card elevation={1} sx={{ borderRadius: 2, mb: 3 }}>
        <CardContent>
          <Typography variant="subtitle1" fontWeight={600} sx={{ mb: 2 }}>Display Configuration</Typography>
          <Divider sx={{ mb: 2 }} />

          <Typography variant="body2" fontWeight={600} sx={{ mb: 1 }}>Rooms to Display:</Typography>
          <Box sx={{ display: 'flex', gap: 2, mb: 3, flexWrap: 'wrap' }}>
            {ROOMS.map(room => (
              <FormControlLabel key={room}
                control={<Checkbox checked={settings.enabled_rooms.includes(room)} onChange={() => handleToggleRoom(room)} />}
                label={room}
              />
            ))}
          </Box>

          <TextField fullWidth type="number" label="Refresh Interval (seconds)"
            value={settings.refresh_interval}
            onChange={(e) => setSettings(s => ({ ...s, refresh_interval: parseInt(e.target.value) || 60 }))}
            sx={{ mb: 2 }} />

          <TextField fullWidth label="Company Name" value={settings.company_name}
            onChange={(e) => setSettings(s => ({ ...s, company_name: e.target.value }))}
            sx={{ mb: 2 }} />

          <TextField fullWidth label="Header Text" value={settings.header_text}
            onChange={(e) => setSettings(s => ({ ...s, header_text: e.target.value }))}
            sx={{ mb: 2 }} />

          <FormControlLabel
            control={<Checkbox checked={settings.show_status_badge} onChange={(e) => setSettings(s => ({ ...s, show_status_badge: e.target.checked }))} />}
            label="Show status badges"
          />
        </CardContent>
      </Card>

      <Box sx={{ display: 'flex', gap: 2 }}>
        <Button variant="contained" startIcon={<SaveIcon />} onClick={handleSave}>Save Settings</Button>
        <Button variant="outlined" href="/meeting-rooms/lcd" target="_blank">Preview LCD Display</Button>
      </Box>
    </Box>
  );
}
