import Box from '@mui/material/Box';
import Button from '@mui/material/Button';

export default function SocialLogin() {
  return (
    <Box sx={{ display: 'flex', gap: 1 }}>
      <Button variant="outlined">Google</Button>
      <Button variant="outlined">Facebook</Button>
    </Box>
  );
}
