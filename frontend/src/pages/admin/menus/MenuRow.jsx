import React from 'react';
import { TableRow, TableCell } from '@mui/material';

export default function MenuRow({ menu }) {
  return (
    <TableRow hover>
      <TableCell>{menu?.name ?? '-'}</TableCell>
      <TableCell>{menu?.route ?? '-'}</TableCell>
      <TableCell>{menu?.status ?? '-'}</TableCell>
    </TableRow>
  );
}
