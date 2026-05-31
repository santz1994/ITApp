import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import { useParams } from 'react-router-dom';

export default function MenuEdit() {
  const { id } = useParams();
  return (
    <Box>
      <Typography variant="h5">Edit Menu {id}</Typography>
    </Box>
  );
}
