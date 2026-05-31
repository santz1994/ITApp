import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Paper from '@mui/material/Paper';
import Typography from '@mui/material/Typography';
import { useParams } from 'react-router-dom';

export default function MeetingRoomPrint() {
  const { id } = useParams();

  return (
    <Box sx={{ p: 3 }}>
      <Paper sx={{ p: 3 }}>
        <Typography variant="h5" gutterBottom>Print Booking</Typography>
        <Typography color="text.secondary" gutterBottom>
          Print view migrated from the legacy Blade screen for booking #{id ?? '-'}.
        </Typography>
        <Button variant="contained" onClick={() => window.print()}>Print</Button>
      </Paper>
    </Box>
  );
}
