import Box from '@mui/material/Box';
import CircularProgress from '@mui/material/CircularProgress';

export default function LoadingSpinner({ size = 24, sx }) {
  return (
    <Box sx={{ display: 'inline-flex', alignItems: 'center', ...sx }}>
      <CircularProgress size={size} />
    </Box>
  );
}
