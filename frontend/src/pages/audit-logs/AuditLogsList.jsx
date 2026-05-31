import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import { useEffect, useState } from 'react';
import { reportApi } from '../../services/api';

export default function AuditLogsList() {
  const [logs, setLogs] = useState([]);
  useEffect(() => { /* TODO: load audit logs */ }, []);
  return (
    <Box>
      <Typography variant="h5">Audit Logs</Typography>
      <Typography sx={{ mt: 2 }}>Placeholder list for audit logs.</Typography>
    </Box>
  );
}
