import React, { useEffect, useState } from 'react';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import { useParams, Link } from 'react-router-dom';
import Button from '@mui/material/Button';
import { userApi } from '../../services/api';

export default function UserShow(){
  const { id } = useParams();
  const [user, setUser] = useState(null);

  useEffect(()=>{ if (id) load(); }, [id]);
  async function load(){ try{ const res = await userApi.getById(id); setUser(res.data.data || res.data); } catch(e){ console.error(e); }}

  if (!user) return <Typography>Loading...</Typography>;

  return (
    <Box>
      <Typography variant="h5">{user.name}</Typography>
      <Typography>Email: {user.email}</Typography>
      <Typography>Roles: {(user.roles || []).join(', ')}</Typography>
      <Button component={Link} to={`/users/${id}/edit`} variant="contained" sx={{ mt:2 }}>Edit</Button>
    </Box>
  );
}
