import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { reportApi } from '../../services/api';

export default function AuditLogShow() {
  const { id } = useParams();
  const [log, setLog] = useState(null);
  useEffect(() => { /* TODO: load audit log */ }, [id]);
  return (
    <Box>
      <Typography variant="h5">Audit Log Detail</Typography>
      <pre>{JSON.stringify(log, null, 2)}</pre>
    </Box>
  );
}
