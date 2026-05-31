import Box from '@mui/material/Box';

export default function ActionButtons({ children, sx }) {
  return (
    <Box sx={{ display: 'flex', gap: 1, flexWrap: 'wrap', ...sx }}>
      {children}
    </Box>
  );
}
