import React from 'react';
import { Box, Paper, Typography } from '@mui/material';

export default function AuditLogsIndex() {
  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Audit Logs
      </Typography>
      <Paper sx={{ p: 3 }}>
        <Typography color="text.secondary">Audit log list placeholder.</Typography>
      </Paper>
    </Box>
  );
}
