import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';

export default function Error500() {
  return (
    <Box sx={{ textAlign: 'center', mt: 8 }}>
      <Typography variant="h2">500</Typography>
      <Typography variant="h6">Terjadi kesalahan server</Typography>
    </Box>
  );
}
