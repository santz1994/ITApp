import AssessmentIcon from '@mui/icons-material/Assessment';
import AssignmentTurnedInIcon from '@mui/icons-material/AssignmentTurnedIn';
import DashboardIcon from '@mui/icons-material/Dashboard';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import Inventory2Icon from '@mui/icons-material/Inventory2';
import ListAltIcon from '@mui/icons-material/ListAlt';
import MeetingRoomIcon from '@mui/icons-material/MeetingRoom';
import PeopleIcon from '@mui/icons-material/People';
import SettingsIcon from '@mui/icons-material/Settings';
import Avatar from '@mui/material/Avatar';
import Box from '@mui/material/Box';
import Divider from '@mui/material/Divider';
import Drawer from '@mui/material/Drawer';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemButton from '@mui/material/ListItemButton';
import ListItemIcon from '@mui/material/ListItemIcon';
import ListItemText from '@mui/material/ListItemText';
import { useTheme } from '@mui/material/styles';
import Toolbar from '@mui/material/Toolbar';
import Typography from '@mui/material/Typography';
import useMediaQuery from '@mui/material/useMediaQuery';
import { useSelector } from 'react-redux';
import { useLocation, useNavigate } from 'react-router-dom';

const DRAWER_WIDTH = 260;

const menuGroups = [
    {
        label: 'Utama',
        items: [
            { text: 'Dashboard', icon: <DashboardIcon />, path: '/' },
        ],
    },
    {
        label: 'Fasilitas',
        items: [
            { text: 'Ruang Meeting', icon: <MeetingRoomIcon />, path: '/meeting-rooms' },
            { text: 'Kendaraan', icon: <DirectionsCarIcon />, path: '/vehicles' },
        ],
    },
    {
        label: 'Inventaris',
        items: [
            { text: 'ATK & Sparepart', icon: <Inventory2Icon />, path: '/inventory' },
            { text: 'Request Inventaris', icon: <ListAltIcon />, path: '/inventory-requests' },
        ],
    },
    {
        label: 'Alur Kerja',
        items: [
            { text: 'Approval', icon: <AssignmentTurnedInIcon />, path: '/approvals', badge: true },
        ],
    },
    {
        label: 'Admin',
        items: [
            { text: 'Manajemen User', icon: <PeopleIcon />, path: '/users' },
            { text: 'Laporan', icon: <AssessmentIcon />, path: '/reports' },
            { text: 'Pengaturan', icon: <SettingsIcon />, path: '/settings' },
        ],
    },
];

function SidebarContent({ onNavigate }) {
    const location = useLocation();
    const navigate = useNavigate();
    const { user } = useSelector((state) => state.auth);
    const { pendingApprovals } = useSelector((state) => state.approvals);

    const handleClick = (path) => {
        navigate(path);
        if (onNavigate) onNavigate();
    };

    const isActive = (path) => {
        if (path === '/') return location.pathname === '/';
        return location.pathname.startsWith(path);
    };

    return (
        <Box sx={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
            {/* Logo */}
            <Box sx={{ px: 2.5, py: 2, display: 'flex', alignItems: 'center', gap: 1.5 }}>
                <Avatar sx={{ bgcolor: 'primary.main', width: 38, height: 38, fontSize: 16, fontWeight: 700 }}>
                    IT
                </Avatar>
                <Box>
                    <Typography variant="subtitle1" fontWeight={700} color="primary.main" lineHeight={1.2}>
                        ITApp
                    </Typography>
                    <Typography variant="caption" color="text.secondary" fontSize={10}>
                        Management System
                    </Typography>
                </Box>
            </Box>

            <Divider />

            {/* User Info */}
            <Box sx={{ px: 2, py: 1.5, display: 'flex', alignItems: 'center', gap: 1.5 }}>
                <Avatar sx={{ width: 32, height: 32, bgcolor: 'secondary.main', fontSize: 13 }}>
                    {user?.name?.[0]?.toUpperCase() || 'U'}
                </Avatar>
                <Box sx={{ minWidth: 0 }}>
                    <Typography variant="body2" fontWeight={600} noWrap>{user?.name || 'User'}</Typography>
                    <Typography variant="caption" color="text.secondary" noWrap>{user?.roles?.[0]?.name || 'Staff'}</Typography>
                </Box>
            </Box>

            <Divider />

            {/* Menu Items */}
            <Box sx={{ flex: 1, overflowY: 'auto', overflowX: 'hidden', py: 1 }}>
                {menuGroups.map((group, gi) => (
                    <Box key={gi}>
                        <Typography
                            variant="overline"
                            sx={{
                                px: 2.5, py: 1, display: 'block',
                                color: 'text.secondary', fontSize: 11, fontWeight: 600,
                                letterSpacing: 1,
                            }}
                        >
                            {group.label}
                        </Typography>
                        <List disablePadding>
                            {group.items.map((item) => (
                                <ListItem key={item.path} disablePadding sx={{ px: 1, mb: 0.25 }}>
                                    <ListItemButton
                                        onClick={() => handleClick(item.path)}
                                        sx={{
                                            borderRadius: 1.5, minHeight: 42,
                                            bgcolor: isActive(item.path) ? 'primary.main' : 'transparent',
                                            color: isActive(item.path) ? 'primary.contrastText' : 'text.primary',
                                            '&:hover': {
                                                bgcolor: isActive(item.path) ? 'primary.dark' : 'action.hover',
                                            },
                                            transition: 'all 0.15s',
                                        }}
                                    >
                                        <ListItemIcon
                                            sx={{
                                                minWidth: 36,
                                                color: isActive(item.path) ? 'primary.contrastText' : 'text.secondary',
                                            }}
                                        >
                                            {item.icon}
                                        </ListItemIcon>
                                        <ListItemText
                                            primary={item.text}
                                            primaryTypographyProps={{ fontSize: 14, fontWeight: isActive(item.path) ? 600 : 500 }}
                                        />
                                        {item.badge && pendingApprovals.length > 0 && (
                                            <Box
                                                sx={{
                                                    bgcolor: 'error.main', color: '#fff',
                                                    borderRadius: 10, minWidth: 20, height: 20,
                                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                    fontSize: 11, fontWeight: 700, px: 0.75,
                                                }}
                                            >
                                                {pendingApprovals.length}
                                            </Box>
                                        )}
                                    </ListItemButton>
                                </ListItem>
                            ))}
                        </List>
                    </Box>
                ))}
            </Box>

            {/* Footer */}
            <Divider />
            <Box sx={{ px: 2, py: 1.5 }}>
                <Typography variant="caption" color="text.secondary" fontSize={10}>
                    ITApp v2.0 &copy; 2026 PT Quty Karunia
                </Typography>
            </Box>
        </Box>
    );
}

function Sidebar({ mobileOpen, onMobileClose, drawerWidth }) {
    const theme = useTheme();
    const isMobile = useMediaQuery(theme.breakpoints.down('md'));

    return (
        <Box component="nav" sx={{ width: { md: drawerWidth }, flexShrink: { md: 0 }, transition: 'width 0.2s' }}>
            {/* Mobile drawer */}
            <Drawer
                variant="temporary"
                open={mobileOpen}
                onClose={onMobileClose}
                ModalProps={{ keepMounted: true }}
                sx={{
                    display: { xs: 'block', md: 'none' },
                    '& .MuiDrawer-paper': { boxSizing: 'border-box', width: DRAWER_WIDTH },
                }}
            >
                <SidebarContent onNavigate={onMobileClose} />
            </Drawer>
            {/* Desktop drawer */}
            <Drawer
                variant="permanent"
                sx={{
                    display: { xs: 'none', md: 'block' },
                    '& .MuiDrawer-paper': { boxSizing: 'border-box', width: drawerWidth, transition: 'width 0.2s', overflowX: 'hidden' },
                }}
                open
            >
                <Toolbar sx={{ minHeight: { xs: 56, sm: 64 } }} />
                <SidebarContent />
            </Drawer>
        </Box>
    );
}

export default Sidebar;