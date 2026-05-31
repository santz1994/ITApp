import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';

export default function Error403() {
  return (
    <Box sx={{ textAlign: 'center', mt: 8 }}>
      <Typography variant="h2">403</Typography>
      <Typography variant="h6">Akses ditolak</Typography>
    </Box>
  );
}
