import AssessmentIcon from '@mui/icons-material/Assessment';
import BusinessIcon from '@mui/icons-material/Business';
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import Inventory2Icon from '@mui/icons-material/Inventory2';
import MeetingRoomIcon from '@mui/icons-material/MeetingRoom';
import PeopleIcon from '@mui/icons-material/People';
import SettingsIcon from '@mui/icons-material/Settings';
import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CircularProgress from '@mui/material/CircularProgress';
import Divider from '@mui/material/Divider';
import Grid from '@mui/material/Grid';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemText from '@mui/material/ListItemText';
import Typography from '@mui/material/Typography';
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api';

const SETTINGS_SECTIONS = [
    {
        title: 'Meeting Rooms',
        icon: <MeetingRoomIcon />,
        items: [
            { label: 'Room Manager', description: 'Add, edit, or remove meeting rooms', path: '/meeting-rooms/rooms' },
            { label: 'LCD Display Settings', description: 'Configure LCD meeting room displays', path: '/meeting-rooms/lcd-settings' },
            { label: 'LCD Maintenance', description: 'Manage LCD display maintenance', path: '/meeting-rooms/lcd-maintenance' },
        ],
    },
    {
        title: 'Vehicles',
        icon: <DirectionsCarIcon />,
        items: [
            { label: 'Vehicle List', description: 'Manage all vehicles', path: '/vehicles' },
        ],
    },
    {
        title: 'Inventory',
        icon: <Inventory2Icon />,
        items: [
            { label: 'Inventory Items', description: 'Manage ATK & Sparepart inventory', path: '/inventory' },
        ],
    },
    {
        title: 'User Management',
        icon: <PeopleIcon />,
        items: [
            { label: 'All Users', description: 'Manage user accounts', path: '/users' },
            { label: 'Roles & Permissions', description: 'Manage roles and permissions', path: '/users/roles' },
        ],
    },
    {
        title: 'Reports',
        icon: <AssessmentIcon />,
        items: [
            { label: 'Audit Logs', description: 'View system audit trail', path: '/audit-logs' },
        ],
    },
    {
        title: 'System',
        icon: <SettingsIcon />,
        items: [
            { label: 'Menu Management', description: 'Configure sidebar menu items', path: '/admin/menus' },
            { label: 'Notification Settings', description: 'Configure notification channels', path: '/admin/notification-settings' },
        ],
    },
];

export default function SystemSettingsPage() {
    const [divisions, setDivisions] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/system-settings/divisions').then(res => {
            setDivisions(res.data?.data || []);
        }).catch(() => { }).finally(() => setLoading(false));
    }, []);

    if (loading) return <Box sx={{ display: 'flex', justifyContent: 'center', py: 8 }}><CircularProgress /></Box>;

    return (
        <Box>
            <Typography variant="h5" fontWeight={700} sx={{ mb: 3 }}>
                <SettingsIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
                Pengaturan Sistem
            </Typography>

            <Grid container spacing={3}>
                {/* System Info */}
                <Grid item xs={12} md={4}>
                    <Card elevation={1} sx={{ borderRadius: 2, mb: 3 }}>
                        <CardContent>
                            <Typography variant="h6" fontWeight={600} sx={{ mb: 1 }}>
                                <BusinessIcon sx={{ mr: 1, verticalAlign: 'middle', fontSize: 20 }} />
                                Company Info
                            </Typography>
                            <Divider sx={{ mb: 2 }} />
                            <Typography variant="body2" sx={{ mb: 1 }}>
                                <strong>Company:</strong> PT Quty Karunia
                            </Typography>
                            <Typography variant="body2" sx={{ mb: 1 }}>
                                <strong>System:</strong> ITApp — Integrated Management System
                            </Typography>
                            <Typography variant="body2" sx={{ mb: 1 }}>
                                <strong>Modules:</strong> Meeting Rooms, Vehicles, Inventory, Approvals
                            </Typography>
                        </CardContent>
                    </Card>

                    <Card elevation={1} sx={{ borderRadius: 2 }}>
                        <CardContent>
                            <Typography variant="h6" fontWeight={600} sx={{ mb: 1 }}>
                                <CalendarMonthIcon sx={{ mr: 1, verticalAlign: 'middle', fontSize: 20 }} />
                                Divisions ({divisions.length})
                            </Typography>
                            <Divider sx={{ mb: 1 }} />
                            <List dense sx={{ maxHeight: 300, overflow: 'auto' }}>
                                {divisions.map(d => (
                                    <ListItem key={d.id} sx={{ py: 0.5 }}>
                                        <ListItemText primary={d.name} />
                                    </ListItem>
                                ))}
                            </List>
                        </CardContent>
                    </Card>
                </Grid>

                {/* Settings Sections */}
                <Grid item xs={12} md={8}>
                    {SETTINGS_SECTIONS.map((section, idx) => (
                        <Card key={idx} elevation={1} sx={{ mb: 2, borderRadius: 2 }}>
                            <CardContent>
                                <Typography variant="subtitle1" fontWeight={600} sx={{ mb: 1 }}>
                                    {section.icon} {section.title}
                                </Typography>
                                <Divider sx={{ mb: 1 }} />
                                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 0.5 }}>
                                    {section.items.map((item, i) => (
                                        <Box key={i} component={Link} to={item.path} sx={{
                                            p: 1.5, borderRadius: 1.5, textDecoration: 'none', color: 'inherit',
                                            border: '1px solid', borderColor: 'divider',
                                            '&:hover': { bgcolor: 'grey.50', borderColor: 'primary.main' },
                                        }}>
                                            <Typography variant="body2" fontWeight={600} color="primary">{item.label}</Typography>
                                            <Typography variant="caption" color="text.secondary">{item.description}</Typography>
                                        </Box>
                                    ))}
                                </Box>
                            </CardContent>
                        </Card>
                    ))}
                </Grid>
            </Grid>
        </Box>
    );
}
