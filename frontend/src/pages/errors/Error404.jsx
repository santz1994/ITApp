import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';

export default function Error404() {
  return (
    <Box sx={{ textAlign: 'center', mt: 8 }}>
      <Typography variant="h2">404</Typography>
      <Typography variant="h6">Halaman tidak ditemukan</Typography>
    </Box>
  );
}
