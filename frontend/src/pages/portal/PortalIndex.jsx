import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import { Link as RouterLink } from 'react-router-dom';

export default function PortalIndex() {
  return (
    <Box>
      <Typography variant="h4">Portal</Typography>
      <Typography sx={{ mt: 2 }}>Welcome to the portal. Use the navigation to access the application.</Typography>
      <Box sx={{ mt: 3 }}>
        <Button component={RouterLink} to="/login" variant="contained">Masuk</Button>
      </Box>
    </Box>
  );
}
