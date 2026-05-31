import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import IconButton from '@mui/material/IconButton';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import { userApi } from '../../services/api';

export default function UserList() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => { fetchUsers(); }, []);

  async function fetchUsers() {
    setLoading(true);
    try {
      const res = await userApi.getAll({ per_page: 50 });
      setUsers(res.data.data || res.data || []);
    } catch (e) { console.error(e); }
    setLoading(false);
  }

  async function removeUser(id) {
    if (!confirm('Delete this user?')) return;
    try { await userApi.delete(id); fetchUsers(); } catch (e) { alert('Delete failed'); }
  }

  return (
    <Box>
      <Typography variant="h5" mb={2}>User Management</Typography>
      <Box sx={{ mb:2 }}>
        <Button component={Link} to="/users/create" variant="contained">New User</Button>
      </Box>
      {loading ? <Typography>Loading…</Typography> : (
        users.length === 0 ? <Typography>No users found.</Typography> : (
          users.map(u => (
            <Box key={u.id} sx={{ p:1, border: '1px solid #eee', mb:1, display:'flex', justifyContent: 'space-between' }}>
              <Box>
                <Typography>{u.name} <Typography component="span" variant="caption">({u.email})</Typography></Typography>
                <Typography variant="caption">Roles: {(u.roles || []).join(', ')}</Typography>
              </Box>
              <Box>
                <IconButton component={Link} to={`/users/${u.id}/edit`} size="small"><EditIcon fontSize="small"/></IconButton>
                <IconButton size="small" onClick={()=>removeUser(u.id)}><DeleteIcon fontSize="small"/></IconButton>
              </Box>
            </Box>
          ))
        )
      )}
    </Box>
  );
}
