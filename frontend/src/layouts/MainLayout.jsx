import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import DashboardIcon from '@mui/icons-material/Dashboard';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import ExpandLess from '@mui/icons-material/ExpandLess';
import ExpandMore from '@mui/icons-material/ExpandMore';
import HistoryIcon from '@mui/icons-material/History';
import Inventory2Icon from '@mui/icons-material/Inventory2';
import LockIcon from '@mui/icons-material/Lock';
import LogoutIcon from '@mui/icons-material/Logout';
import MenuIcon from '@mui/icons-material/Menu';
import NotificationsIcon from '@mui/icons-material/Notifications';
import PeopleIcon from '@mui/icons-material/People';
import PersonIcon from '@mui/icons-material/Person';
import PhotoCameraIcon from '@mui/icons-material/PhotoCamera';
import SettingsIcon from '@mui/icons-material/Settings';
import AppBar from '@mui/material/AppBar';
import Avatar from '@mui/material/Avatar';
import Box from '@mui/material/Box';
import Collapse from '@mui/material/Collapse';
import CssBaseline from '@mui/material/CssBaseline';
import Divider from '@mui/material/Divider';
import Drawer from '@mui/material/Drawer';
import IconButton from '@mui/material/IconButton';
import List from '@mui/material/List';
import ListItemButton from '@mui/material/ListItemButton';
import ListItemIcon from '@mui/material/ListItemIcon';
import ListItemText from '@mui/material/ListItemText';
import Menu from '@mui/material/Menu';
import MenuItem from '@mui/material/MenuItem';
import Toolbar from '@mui/material/Toolbar';
import Tooltip from '@mui/material/Tooltip';
import Typography from '@mui/material/Typography';
import { alpha } from '@mui/material/styles';
import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { logout } from '../../store/slices/authSlice';

const DRAWER_WIDTH = 270;
const COLLAPSED_WIDTH = 68;

const NAV_SECTIONS = [
    {
        label: 'Dashboard',
        icon: <DashboardIcon />,
        path: '/home',
    },
    {
        label: 'Meeting Rooms',
        icon: <CalendarMonthIcon />,
        children: [
            { label: 'All Bookings', path: '/meeting-rooms' },
            { label: 'New Booking', path: '/meeting-rooms/create' },
            { label: 'Calendar View', path: '/meeting-rooms/calendar' },
            { label: 'Director Dashboard', path: '/meeting-rooms/d-dashboard' },
            { label: 'Receptionist Dashboard', path: '/meeting-rooms/r-dashboard' },
            { label: 'LCD Display', path: '/meeting-rooms/lcd' },
            { label: 'LCD Settings', path: '/meeting-rooms/lcd-settings' },
        ],
    },
    {
        label: 'Vehicles',
        icon: <DirectionsCarIcon />,
        children: [
            { label: 'Vehicle List', path: '/vehicles' },
            { label: 'New Vehicle', path: '/vehicles/create' },
            { label: 'New Booking', path: '/vehicle-bookings/create' },
            { label: 'My Bookings', path: '/my-bookings' },
        ],
    },
    {
        label: 'Inventory',
        icon: <Inventory2Icon />,
        children: [
            { label: 'Item List', path: '/inventory' },
            { label: 'New Item', path: '/inventory/create' },
            { label: 'My Requests', path: '/inventory-requests' },
            { label: 'New Request', path: '/inventory-requests/create' },
        ],
    },
    {
        label: 'Approvals',
        icon: <CheckCircleIcon />,
        path: '/approvals',
    },
    {
        label: 'User Management',
        icon: <PeopleIcon />,
        children: [
            { label: 'All Users', path: '/users' },
            { label: 'Add User', path: '/users/create' },
            { label: 'Roles & Permissions', path: '/users/roles' },
        ],
    },
    {
        label: 'Administration',
        icon: <SettingsIcon />,
        children: [
            { label: 'Admin Dashboard', path: '/admin' },
            { label: 'Menu Management', path: '/admin/menus' },
            { label: 'Notification Settings', path: '/admin/notification-settings' },
            { label: 'System Settings', path: '/admin/system-settings' },
        ],
    },
    {
        label: 'Audit Logs',
        icon: <HistoryIcon />,
        path: '/audit-logs',
    },
];

function SidebarSection({ section, open, expanded, onToggle }) {
    const location = useLocation();
    const hasChildren = section.children && section.children.length > 0;
    const isActive = hasChildren
        ? section.children.some(c => location.pathname.startsWith(c.path))
        : location.pathname === section.path;
    const isExpanded = expanded[section.label];

    if (!hasChildren) {
        return (
            <ListItemButton
                component={Link}
                to={section.path}
                selected={isActive}
                sx={{
                    minHeight: 44,
                    px: 2,
                    mx: 1,
                    my: 0.3,
                    borderRadius: 1.5,
                    color: isActive ? 'primary.main' : 'text.secondary',
                    bgcolor: isActive ? (t => alpha(t.palette.primary.main, 0.08)) : 'transparent',
                    '&:hover': { bgcolor: (t) => alpha(t.palette.primary.main, 0.06) },
                    justifyContent: open ? 'initial' : 'center',
                }}
            >
                <ListItemIcon sx={{ minWidth: 0, mr: open ? 1.5 : 'auto', justifyContent: 'center', color: isActive ? 'primary.main' : 'inherit' }}>
                    {section.icon}
                </ListItemIcon>
                {open && <ListItemText primary={section.label} primaryTypographyProps={{ fontWeight: isActive ? 600 : 400, fontSize: 14 }} />}
            </ListItemButton>
        );
    }

    return (
        <>
            <ListItemButton
                onClick={() => onToggle(section.label)}
                sx={{
                    minHeight: 44, px: 2, mx: 1, my: 0.3, borderRadius: 1.5,
                    color: isActive ? 'primary.main' : 'text.secondary',
                    bgcolor: isActive ? (t => alpha(t.palette.primary.main, 0.08)) : 'transparent',
                    justifyContent: open ? 'initial' : 'center',
                }}
            >
                <ListItemIcon sx={{ minWidth: 0, mr: open ? 1.5 : 'auto', justifyContent: 'center', color: isActive ? 'primary.main' : 'inherit' }}>
                    {section.icon}
                </ListItemIcon>
                {open && (
                    <>
                        <ListItemText primary={section.label} primaryTypographyProps={{ fontWeight: isActive ? 600 : 500, fontSize: 14 }} />
                        {isExpanded ? <ExpandLess /> : <ExpandMore />}
                    </>
                )}
            </ListItemButton>
            {open && (
                <Collapse in={isExpanded} timeout="auto" unmountOnExit>
                    <List disablePadding sx={{ pl: 1 }}>
                        {section.children.map((child) => {
                            const childActive = location.pathname === child.path || location.pathname.startsWith(child.path + '/');
                            return (
                                <ListItemButton
                                    key={child.path}
                                    component={Link}
                                    to={child.path}
                                    selected={childActive}
                                    sx={{
                                        minHeight: 36, pl: 4, mx: 1, my: 0.1, borderRadius: 1.5,
                                        color: childActive ? 'primary.main' : 'text.secondary',
                                        bgcolor: childActive ? (t => alpha(t.palette.primary.main, 0.06)) : 'transparent',
                                        '&:hover': { bgcolor: (t) => alpha(t.palette.primary.main, 0.04) },
                                    }}
                                >
                                    <ListItemText primary={child.label} primaryTypographyProps={{ fontSize: 13.5, fontWeight: childActive ? 600 : 400 }} />
                                </ListItemButton>
                            );
                        })}
                    </List>
                </Collapse>
            )}
        </>
    );
}

export default function MainLayout() {
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const location = useLocation();
    const user = useSelector((s) => s.auth.user);
    const [drawerOpen, setDrawerOpen] = useState(true);
    const [expanded, setExpanded] = useState({});
    const [anchorEl, setAnchorEl] = useState(null);

    // Auto-expand active section on mount/route change
    useEffect(() => {
        NAV_SECTIONS.forEach((section) => {
            if (section.children?.some(c => location.pathname.startsWith(c.path))) {
                setExpanded((prev) => ({ ...prev, [section.label]: true }));
            }
        });
    }, [location.pathname]);

    const handleToggle = (label) => setExpanded((prev) => ({ ...prev, [label]: !prev[label] }));

    const handleLogout = async () => {
        await dispatch(logout());
        navigate('/login');
    };

    return (
        <Box sx={{ display: 'flex' }}>
            <CssBaseline />

            {/* Top Bar */}
            <AppBar position="fixed" elevation={1} sx={{ bgcolor: '#fff', color: 'text.primary', zIndex: (t) => t.zIndex.drawer + 1 }}>
                <Toolbar>
                    <IconButton onClick={() => setDrawerOpen(!drawerOpen)} sx={{ mr: 1 }}>
                        <MenuIcon />
                    </IconButton>
                    <Typography variant="h6" noWrap sx={{ fontWeight: 700, color: 'primary.main', letterSpacing: -0.5 }}>
                        ITApp
                    </Typography>
                    <Box sx={{ flexGrow: 1 }} />
                    <Typography variant="body2" sx={{ mr: 1, color: 'text.secondary' }}>
                        {user?.name}
                    </Typography>
                    <Tooltip title="Account">
                        <IconButton onClick={(e) => setAnchorEl(e.currentTarget)} size="small">
                            <Avatar sx={{ width: 34, height: 34, bgcolor: 'primary.main', fontSize: 14 }}>
                                {user?.name?.[0]?.toUpperCase() || 'U'}
                            </Avatar>
                        </IconButton>
                    </Tooltip>
                    <Menu anchorEl={anchorEl} open={Boolean(anchorEl)} onClose={() => setAnchorEl(null)}>
                        <MenuItem component={Link} to="/profile" onClick={() => setAnchorEl(null)}>
                            <PersonIcon fontSize="small" sx={{ mr: 1 }} /> Profile
                        </MenuItem>
                        <MenuItem component={Link} to="/profile/change-password" onClick={() => setAnchorEl(null)}>
                            <LockIcon fontSize="small" sx={{ mr: 1 }} /> Change Password
                        </MenuItem>
                        <MenuItem component={Link} to="/profile/change-picture" onClick={() => setAnchorEl(null)}>
                            <PhotoCameraIcon fontSize="small" sx={{ mr: 1 }} /> Profile Picture
                        </MenuItem>
                        <MenuItem component={Link} to="/profile/notifications" onClick={() => setAnchorEl(null)}>
                            <NotificationsIcon fontSize="small" sx={{ mr: 1 }} /> Notifications
                        </MenuItem>
                        <Divider />
                        <MenuItem onClick={handleLogout} sx={{ color: 'error.main' }}>
                            <LogoutIcon fontSize="small" sx={{ mr: 1 }} /> Logout
                        </MenuItem>
                    </Menu>
                </Toolbar>
            </AppBar>

            {/* Sidebar */}
            <Drawer
                variant="permanent"
                open={drawerOpen}
                sx={{
                    width: drawerOpen ? DRAWER_WIDTH : COLLAPSED_WIDTH,
                    flexShrink: 0,
                    '& .MuiDrawer-paper': {
                        width: drawerOpen ? DRAWER_WIDTH : COLLAPSED_WIDTH,
                        boxSizing: 'border-box',
                        borderRight: '1px solid',
                        borderColor: 'divider',
                        bgcolor: '#f8fafc',
                        pt: '64px',
                        transition: 'width 0.2s',
                        overflowX: 'hidden',
                    },
                }}
            >
                {/* User mini-card */}
                {drawerOpen && (
                    <Box sx={{ p: 2, display: 'flex', alignItems: 'center', gap: 1.5, borderBottom: '1px solid', borderColor: 'divider' }}>
                        <Avatar sx={{ width: 38, height: 38, bgcolor: 'primary.main', fontSize: 15 }}>
                            {user?.name?.[0]?.toUpperCase() || 'U'}
                        </Avatar>
                        <Box sx={{ minWidth: 0 }}>
                            <Typography variant="subtitle2" noWrap fontWeight={600}>{user?.name || 'User'}</Typography>
                            <Typography variant="caption" color="text.secondary" noWrap>
                                {user?.roles?.[0]?.name || 'Staff'}
                            </Typography>
                        </Box>
                    </Box>
                )}

                {/* Navigation */}
                <List component="nav" sx={{ pt: 1, pb: 2, flex: 1 }}>
                    <Typography variant="overline" sx={{ px: 2, py: 1, color: 'text.disabled', fontSize: 11, fontWeight: 700, display: drawerOpen ? 'block' : 'none' }}>
                        Navigation
                    </Typography>
                    {NAV_SECTIONS.map((section) => (
                        <SidebarSection
                            key={section.label}
                            section={section}
                            open={drawerOpen}
                            expanded={expanded}
                            onToggle={handleToggle}
                        />
                    ))}
                </List>
            </Drawer>

            {/* Main Content */}
            <Box component="main" sx={{ flexGrow: 1, p: 3, pt: '88px', minHeight: '100vh', bgcolor: 'grey.50' }}>
                <Outlet />
            </Box>
        </Box>
    );
}
