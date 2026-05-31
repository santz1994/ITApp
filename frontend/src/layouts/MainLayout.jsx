import AppBar from '@mui/material/AppBar';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Container from '@mui/material/Container';
import Toolbar from '@mui/material/Toolbar';
import Typography from '@mui/material/Typography';
import { useDispatch } from 'react-redux';
import { Link, Outlet, useNavigate } from 'react-router-dom';
import { setAuthToken } from '../api/client';
import { clearCredentials } from '../store/authSlice';

export default function MainLayout() {
    const dispatch = useDispatch();
    const navigate = useNavigate();

    function handleLogout() {
        dispatch(clearCredentials());
        setAuthToken(null);
        try { localStorage.removeItem('itapp_auth'); } catch (e) { }
        navigate('/login');
    }

    return (
        <div>
            <AppBar position="static">
                <Toolbar>
                    <Typography variant="h6" component="div" sx={{ flexGrow: 1 }}>
                        ITApp
                    </Typography>
                    <Button color="inherit" component={Link} to="/meeting-rooms">Meeting Rooms</Button>
                    <Button color="inherit" onClick={handleLogout}>Logout</Button>
                </Toolbar>
            </AppBar>
            <Container sx={{ mt: 3 }}>
                <Box>
                    <Outlet />
                </Box>
            </Container>
        </div>
    );
}
