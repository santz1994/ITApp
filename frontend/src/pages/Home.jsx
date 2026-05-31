import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';

export default function Home() {
  return (
    <Box>
      <Typography variant="h4">Home</Typography>
      <Typography sx={{ mt: 2 }}>This is the React replacement for home.blade.php. Use Dashboard for app landing.</Typography>
    </Box>
  );
}
