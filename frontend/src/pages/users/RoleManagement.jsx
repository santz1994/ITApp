import React, { useEffect, useState } from 'react';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import { userApi } from '../../services/api';

export default function RoleManagement(){
  const [roles, setRoles] = useState([]);
  const [newRole, setNewRole] = useState('');

  useEffect(()=>{ fetchRoles(); }, []);
  async function fetchRoles(){ try { const res = await userApi.getRoles(); setRoles(res.data || []); } catch(e){ console.error(e); } }

  async function addRole(){ if (!newRole) return; try { await userApi.create({ name: newRole }); setNewRole(''); fetchRoles(); } catch(e){ alert('Failed'); } }

  return (
    <Box>
      <Typography variant="h5">Role Management</Typography>
      <Box sx={{ mt:2, display:'flex', gap:1 }}>
        <TextField label="New role" value={newRole} onChange={e=>setNewRole(e.target.value)} />
        <Button variant="contained" onClick={addRole}>Add</Button>
      </Box>
      <Box sx={{ mt:2 }}>
        {roles.map(r=> <Box key={r} sx={{ p:1, border:'1px solid #eee', mb:1 }}>{r}</Box>)}
      </Box>
    </Box>
  );
}
