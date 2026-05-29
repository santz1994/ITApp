import { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Typography from '@mui/material/Typography';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import Alert from '@mui/material/Alert';
import InputAdornment from '@mui/material/InputAdornment';
import IconButton from '@mui/material/IconButton';
import CircularProgress from '@mui/material/CircularProgress';
import EmailIcon from '@mui/icons-material/Email';
import LockIcon from '@mui/icons-material/Lock';
import VisibilityIcon from '@mui/icons-material/Visibility';
import VisibilityOffIcon from '@mui/icons-material/VisibilityOff';
import BusinessIcon from '@mui/icons-material/Business';
import { clearAuthError, login } from '../store/slices/authSlice';

function Login() {
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const { loading, error, isAuthenticated } = useSelector((state) => state.auth);
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);

    useEffect(() => {
        if (isAuthenticated) navigate('/', { replace: true });
    }, [isAuthenticated, navigate]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        dispatch(clearAuthError());
        const result = await dispatch(login({ email, password }));
        if (result.meta.requestStatus === 'fulfilled') navigate('/');
    };

    return (
        <Box sx={{ minHeight: '100vh', display: 'flex' }}>
            {/* Left Panel - Branding */}
            <Box sx={{
                flex: { xs: 0, md: 1 }, display: { xs: 'none', md: 'flex' },
                flexDirection: 'column', justifyContent: 'center', alignItems: 'center',
                background: 'linear-gradient(135deg, #1565c0 0%, #003c8f 100%)',
                color: 'white', p: 6, position: 'relative', overflow: 'hidden',
            }}>
                <Box sx={{ position: 'absolute', top: -100, right: -100, width: 300, height: 300, borderRadius: '50%', bgcolor: 'rgba(255,255,255,0.05)' }} />
                <Box sx={{ position: 'absolute', bottom: -80, left: -80, width: 250, height: 250, borderRadius: '50%', bgcolor: 'rgba(255,255,255,0.05)' }} />
                <BusinessIcon sx={{ fontSize: 80, mb: 3, opacity: 0.9 }} />
                <Typography variant="h3" fontWeight={700} gutterBottom>ITApp</Typography>
                <Typography variant="h6" fontWeight={400} sx={{ opacity: 0.85, textAlign: 'center', maxWidth: 400 }}>
                    Integrated Management System
                </Typography>
                <Typography variant="body1" sx={{ opacity: 0.7, textAlign: 'center', maxWidth: 350, mt: 1 }}>
                    PT Quty Karunia — Booking Ruang Meeting, Kendaraan, Manajemen Inventaris & Approval Workflow
                </Typography>
            </Box>

            {/* Right Panel - Login Form */}
            <Box sx={{
                flex: { xs: 1, md: 0.8 }, display: 'flex', alignItems: 'center', justifyContent: 'center',
                bgcolor: '#f8fafc', p: 3,
            }}>
                <Card sx={{ maxWidth: 440, width: '100%', p: { xs: 2, sm: 4 } }} elevation={0}>
                    <CardContent>
                        <Box sx={{ display: { md: 'none' }, textAlign: 'center', mb: 3 }}>
                            <Typography variant="h5" fontWeight={700} color="primary">ITApp</Typography>
                        </Box>

                        <Typography variant="h5" fontWeight={700} gutterBottom sx={{ mb: 0.5 }}>
                            Selamat Datang
                        </Typography>
                        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                            Silakan masuk ke akun Anda
                        </Typography>

                        {error && <Alert severity="error" sx={{ mb: 2, borderRadius: 2 }}>{error}</Alert>}

                        <Box component="form" onSubmit={handleSubmit}>
                            <TextField
                                fullWidth label="Email" type="email" value={email}
                                onChange={(e) => setEmail(e.target.value)} required
                                sx={{ mb: 2 }}
                                InputProps={{
                                    startAdornment: <InputAdornment position="start"><EmailIcon color="action" /></InputAdornment>,
                                }}
                            />
                            <TextField
                                fullWidth label="Password" type={showPassword ? 'text' : 'password'}
                                value={password} onChange={(e) => setPassword(e.target.value)} required
                                sx={{ mb: 3 }}
                                InputProps={{
                                    startAdornment: <InputAdornment position="start"><LockIcon color="action" /></InputAdornment>,
                                    endAdornment: (
                                        <InputAdornment position="end">
                                            <IconButton onClick={() => setShowPassword(!showPassword)} edge="end" size="small">
                                                {showPassword ? <VisibilityOffIcon /> : <VisibilityIcon />}
                                            </IconButton>
                                        </InputAdornment>
                                    ),
                                }}
                            />
                            <Button
                                type="submit" fullWidth variant="contained" size="large"
                                disabled={loading}
                                sx={{ py: 1.5, fontSize: 16, borderRadius: 2 }}
                            >
                                {loading ? <CircularProgress size={24} color="inherit" /> : 'Masuk'}
                            </Button>
                        </Box>

                        <Typography variant="caption" color="text.secondary" sx={{ display: 'block', textAlign: 'center', mt: 4 }}>
                            &copy; 2026 PT Quty Karunia
                        </Typography>
                    </CardContent>
                </Card>
            </Box>
        </Box>
    );
}

export default Login;