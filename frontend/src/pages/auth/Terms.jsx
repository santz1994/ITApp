import Box from '@mui/material/Box';
import Paper from '@mui/material/Paper';
import Typography from '@mui/material/Typography';

export default function Terms() {
  return (
    <Box sx={{ maxWidth: 960, mx: 'auto', px: 2, py: 4 }}>
      <Paper sx={{ p: 4 }}>
        <Typography variant="h4" gutterBottom>Terms & Conditions</Typography>
        <Typography color="text.secondary">
          Placeholder for the public terms page from the legacy Blade view.
        </Typography>
      </Paper>
    </Box>
  );
}
