import LockIcon from '@mui/icons-material/Lock';
import LogoutIcon from '@mui/icons-material/Logout';
import MenuIcon from '@mui/icons-material/Menu';
import PersonIcon from '@mui/icons-material/Person';
import AppBar from '@mui/material/AppBar';
import Avatar from '@mui/material/Avatar';
import Box from '@mui/material/Box';
import Chip from '@mui/material/Chip';
import Divider from '@mui/material/Divider';
import IconButton from '@mui/material/IconButton';
import ListItemIcon from '@mui/material/ListItemIcon';
import Menu from '@mui/material/Menu';
import MenuItem from '@mui/material/MenuItem';
import Toolbar from '@mui/material/Toolbar';
import Tooltip from '@mui/material/Tooltip';
import Typography from '@mui/material/Typography';
import { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { logout } from '../../store/slices/authSlice';

function Header({ drawerWidth, onMobileMenuToggle }) {
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const { user } = useSelector((state) => state.auth);
    const { sidebarCollapsed } = useSelector((state) => state.ui);
    const [anchorEl, setAnchorEl] = useState(null);

    const handleMenu = (event) => setAnchorEl(event.currentTarget);
    const handleClose = () => setAnchorEl(null);

    const handleLogout = () => {
        handleClose();
        dispatch(logout());
    };

    const handleProfile = () => {
        handleClose();
        navigate('/profile');
    };

    const handleChangePassword = () => {
        handleClose();
        navigate('/profile/change-password');
    };

    const getInitials = (name) => {
        if (!name) return 'U';
        return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    };

    const getRoleBadge = () => {
        const roleName = user?.roles?.[0]?.name || 'User';
        return roleName.charAt(0).toUpperCase() + roleName.slice(1);
    };

    return (
        <AppBar
            position="fixed"
            elevation={0}
            sx={{
                width: { md: `calc(100% - ${drawerWidth}px)` },
                ml: { md: `${drawerWidth}px` },
                bgcolor: '#ffffff',
                color: 'text.primary',
                borderBottom: '1px solid',
                borderColor: 'divider',
                transition: 'width 0.2s, margin 0.2s',
            }}
        >
            <Toolbar sx={{ minHeight: { xs: 56, sm: 64 } }}>
                <IconButton
                    color="inherit"
                    edge="start"
                    onClick={onMobileMenuToggle}
                    sx={{ mr: 2, display: { md: 'none' } }}
                >
                    <MenuIcon />
                </IconButton>

                <Box sx={{ flexGrow: 1 }} />

                <Chip
                    label={getRoleBadge()}
                    size="small"
                    color="primary"
                    variant="outlined"
                    sx={{ mr: 2, fontWeight: 500, display: { xs: 'none', sm: 'flex' } }}
                />

                <Tooltip title="Akun">
                    <IconButton onClick={handleMenu} sx={{ p: 0 }}>
                        <Avatar
                            sx={{
                                width: 36, height: 36,
                                bgcolor: 'primary.main',
                                fontSize: 14,
                                fontWeight: 600,
                            }}
                        >
                            {getInitials(user?.name)}
                        </Avatar>
                    </IconButton>
                </Tooltip>

                <Menu
                    anchorEl={anchorEl}
                    open={Boolean(anchorEl)}
                    onClose={handleClose}
                    PaperProps={{
                        elevation: 3,
                        sx: { mt: 1.5, minWidth: 200, borderRadius: 2, overflow: 'visible' },
                    }}
                    transformOrigin={{ horizontal: 'right', vertical: 'top' }}
                    anchorOrigin={{ horizontal: 'right', vertical: 'bottom' }}
                >
                    <Box sx={{ px: 2, py: 1 }}>
                        <Typography variant="subtitle2" fontWeight={600}>{user?.name || 'User'}</Typography>
                        <Typography variant="caption" color="text.secondary">{user?.email || ''}</Typography>
                    </Box>
                    <Divider />
                    <MenuItem onClick={handleProfile}>
                        <ListItemIcon><PersonIcon fontSize="small" /></ListItemIcon>
                        Profile
                    </MenuItem>
                    <MenuItem onClick={handleChangePassword}>
                        <ListItemIcon><LockIcon fontSize="small" /></ListItemIcon>
                        Ubah Password
                    </MenuItem>
                    <Divider />
                    <MenuItem onClick={handleLogout} sx={{ color: 'error.main' }}>
                        <ListItemIcon><LogoutIcon fontSize="small" sx={{ color: 'error.main' }} /></ListItemIcon>
                        Logout
                    </MenuItem>
                </Menu>
            </Toolbar>
        </AppBar>
    );
}

export default Header;