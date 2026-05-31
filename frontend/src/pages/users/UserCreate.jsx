import React, { useEffect, useState } from 'react';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import MenuItem from '@mui/material/MenuItem';
import Typography from '@mui/material/Typography';
import { useNavigate, useParams } from 'react-router-dom';
import { userApi } from '../../services/api';

export default function UserCreate() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [user, setUser] = useState({ name: '', email: '', roles: [] });
  const [roles, setRoles] = useState([]);

  useEffect(() => { fetchRoles(); if (id) fetchUser(); }, [id]);

  async function fetchRoles() { try { const r = await userApi.getRoles(); setRoles(r.data || []); } catch(e){ console.error(e); } }
  async function fetchUser(){ try { const res = await userApi.getById(id); setUser(res.data.data || res.data); } catch(e){ console.error(e); } }

  async function submit(e) { e.preventDefault(); try {
    if (id) await userApi.update(id, user); else await userApi.create(user);
    navigate('/users');
  } catch (err) { alert('Save failed'); }}

  return (
    <Box component="form" onSubmit={submit} sx={{ display:'flex', flexDirection:'column', gap:2 }}>
      <Typography variant="h5">{id ? 'Edit User' : 'Create User'}</Typography>
      <TextField label="Name" value={user.name} onChange={e=>setUser({...user, name: e.target.value})} required />
      <TextField label="Email" value={user.email} onChange={e=>setUser({...user, email: e.target.value})} required />
      <TextField select label="Role" value={user.roles?.[0] || ''} onChange={e=>setUser({...user, roles: [e.target.value]})}>
        <MenuItem value="">Select</MenuItem>
        {roles.map(r=> <MenuItem key={r} value={r}>{r}</MenuItem>)}
      </TextField>
      <Box>
        <Button variant="outlined" onClick={()=>navigate('/users')} sx={{ mr:1 }}>Cancel</Button>
        <Button type="submit" variant="contained">Save</Button>
      </Box>
    </Box>
  );
}
