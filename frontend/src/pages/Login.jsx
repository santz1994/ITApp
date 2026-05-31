import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import Typography from '@mui/material/Typography';
import { useState } from 'react';
import { useDispatch } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import api, { setAuthToken } from '../api/client';
import { setCredentials } from '../store/authSlice';

export default function Login() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState(null);
    const dispatch = useDispatch();
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const res = await api.post('/api/login', { email, password });
            const { token, user } = res.data;
            dispatch(setCredentials({ token, user }));
            setAuthToken(token);
            try {
                localStorage.setItem('itapp_auth', JSON.stringify({ token, user }));
            } catch (err) { }
            navigate('/home');
        } catch (err) {
            setError(err.response?.data?.message || 'Login failed');
        }
    };

    return (
        <Box maxWidth={420} mx="auto" mt={8}>
            <Typography variant="h5" mb={2}>Sign in</Typography>
            <form onSubmit={handleSubmit}>
                <TextField label="Email" fullWidth margin="normal" value={email} onChange={(e) => setEmail(e.target.value)} />
                <TextField label="Password" type="password" fullWidth margin="normal" value={password} onChange={(e) => setPassword(e.target.value)} />
                {error && <Typography color="error" variant="body2">{error}</Typography>}
                <Button type="submit" variant="contained" sx={{ mt: 2 }}>Login</Button>
            </form>
        </Box>
    );
}
