import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';

export default function Error503() {
  return (
    <Box sx={{ textAlign: 'center', mt: 8 }}>
      <Typography variant="h2">503</Typography>
      <Typography variant="h6">Layanan tidak tersedia</Typography>
    </Box>
  );
}
