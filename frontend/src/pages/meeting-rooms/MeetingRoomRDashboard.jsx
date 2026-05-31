import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Paper from '@mui/material/Paper';
import Stack from '@mui/material/Stack';
import Typography from '@mui/material/Typography';
import { useNavigate } from 'react-router-dom';

export default function MeetingRoomRDashboard() {
  const navigate = useNavigate();

  return (
    <Box sx={{ p: 3 }}>
      <Typography variant="h5" gutterBottom>Meeting Room Dashboard</Typography>
      <Paper sx={{ p: 3 }}>
        <Stack spacing={2}>
          <Typography color="text.secondary">
            Placeholder dashboard migrated from the legacy `r-dashboard` Blade view.
          </Typography>
          <Stack direction="row" spacing={1}>
            <Button variant="contained" onClick={() => navigate('/meeting-rooms')}>Bookings</Button>
            <Button variant="outlined" onClick={() => navigate('/meeting-rooms/calendar')}>Calendar</Button>
          </Stack>
        </Stack>
      </Paper>
    </Box>
  );
}
