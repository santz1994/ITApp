import Paper from '@mui/material/Paper';
import Typography from '@mui/material/Typography';

export default function DataTable({ children }) {
  return (
    <Paper sx={{ p: 2 }}>
      {children || <Typography>Table placeholder</Typography>}
    </Paper>
  );
}
