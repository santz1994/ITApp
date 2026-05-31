import Box from '@mui/material/Box';
import Paper from '@mui/material/Paper';
import Typography from '@mui/material/Typography';

export default function MeetingRoomDDashboard() {
  return (
    <Box sx={{ p: 3 }}>
      <Typography variant="h5" gutterBottom>Display Dashboard</Typography>
      <Paper sx={{ p: 3 }}>
        <Typography color="text.secondary">
          Placeholder display dashboard migrated from the legacy `d-dashboard` Blade view.
        </Typography>
      </Paper>
    </Box>
  );
}
