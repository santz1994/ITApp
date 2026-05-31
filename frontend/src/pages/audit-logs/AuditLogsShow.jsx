import React from 'react';
import { Box, Paper, Typography } from '@mui/material';

export default function AuditLogsShow() {
  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Audit Log Details
      </Typography>
      <Paper sx={{ p: 3 }}>
        <Typography color="text.secondary">Audit log detail placeholder.</Typography>
      </Paper>
    </Box>
  );
}
