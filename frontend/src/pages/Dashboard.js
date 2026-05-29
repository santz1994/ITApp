import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import Box from '@mui/material/Box';
import Grid from '@mui/material/Grid';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardActionArea from '@mui/material/CardActionArea';
import Typography from '@mui/material/Typography';
import Avatar from '@mui/material/Avatar';
import Chip from '@mui/material/Chip';
import Button from '@mui/material/Button';
import Divider from '@mui/material/Divider';
import MeetingRoomIcon from '@mui/icons-material/MeetingRoom';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import Inventory2Icon from '@mui/icons-material/Inventory2';
import AssignmentTurnedInIcon from '@mui/icons-material/AssignmentTurnedIn';
import WarningAmberIcon from '@mui/icons-material/WarningAmber';
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import AddIcon from '@mui/icons-material/Add';
import TrendingUpIcon from '@mui/icons-material/TrendingUp';
import { fetchPendingApprovals } from '../store/slices/approvalSlice';
import { fetchVehicles } from '../store/slices/vehicleSlice';
import { fetchItems } from '../store/slices/inventorySlice';

const StatCard = ({ icon, title, value, color, onClick }) => (
    <Card sx={{ height: '100%' }}>
        <CardActionArea onClick={onClick} sx={{ height: '100%', p: 2.5 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                <Avatar sx={{ bgcolor: `${color}.lighter`, color: `${color}.main`, width: 52, height: 52 }}>
                    {icon}
                </Avatar>
                <Box>
                    <Typography variant="h4" fontWeight={700}>{value}</Typography>
                    <Typography variant="body2" color="text.secondary" fontWeight={500}>{title}</Typography>
                </Box>
            </Box>
        </CardActionArea>
    </Card>
);

const QuickAction = ({ icon, label, color, onClick }) => (
    <Button
        variant="outlined" color={color} onClick={onClick} startIcon={icon}
        fullWidth sx={{ py: 1.5, borderRadius: 2, fontWeight: 600 }}
    >
        {label}
    </Button>
);

function Dashboard() {
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const { user } = useSelector((state) => state.auth);
    const { vehicles } = useSelector((state) => state.vehicles);
    const { items, stats } = useSelector((state) => state.inventory);
    const { pendingApprovals } = useSelector((state) => state.approvals);

    useEffect(() => {
        dispatch(fetchVehicles());
        dispatch(fetchItems());
        dispatch(fetchPendingApprovals());
    }, [dispatch]);

    const greeting = () => {
        const hour = new Date().getHours();
        if (hour < 12) return 'Selamat Pagi';
        if (hour < 15) return 'Selamat Siang';
        if (hour < 18) return 'Selamat Sore';
        return 'Selamat Malam';
    };

    return (
        <Box>
            {/* Welcome Banner */}
            <Card sx={{ mb: 3, background: 'linear-gradient(135deg, #1565c0 0%, #1976d2 50%, #42a5f5 100%)', color: 'white' }}>
                <CardContent sx={{ py: 4, px: 4 }}>
                    <Typography variant="h4" fontWeight={700}>{greeting()}, {user?.name?.split(' ')[0] || 'User'}! 👋</Typography>
                    <Typography variant="body1" sx={{ opacity: 0.85, mt: 0.5 }}>
                        Integrated Management System — PT Quty Karunia
                    </Typography>
                    {pendingApprovals.length > 0 && (
                        <Chip
                            label={`${pendingApprovals.length} approval menunggu`}
                            color="warning" sx={{ mt: 2, fontWeight: 600 }}
                            onClick={() => navigate('/approvals')}
                        />
                    )}
                </CardContent>
            </Card>

            {/* Stat Cards - Bento Grid */}
            <Grid container spacing={2} sx={{ mb: 3 }}>
                <Grid item xs={12} sm={6} md={3}>
                    <StatCard icon={<MeetingRoomIcon />} title="Ruang Meeting" value="3 Ruangan" color="primary" onClick={() => navigate('/meeting-rooms')} />
                </Grid>
                <Grid item xs={12} sm={6} md={3}>
                    <StatCard icon={<DirectionsCarIcon />} title="Kendaraan" value={vehicles.length} color="success" onClick={() => navigate('/vehicles')} />
                </Grid>
                <Grid item xs={12} sm={6} md={3}>
                    <StatCard icon={<Inventory2Icon />} title="Item Inventaris" value={stats.total_items || items.length} color="info" onClick={() => navigate('/inventory')} />
                </Grid>
                <Grid item xs={12} sm={6} md={3}>
                    <StatCard icon={<AssignmentTurnedInIcon />} title="Pending Approval" value={pendingApprovals.length} color="error" onClick={() => navigate('/approvals')} />
                </Grid>
            </Grid>

            <Grid container spacing={2}>
                {/* Quick Actions - Bento block */}
                <Grid item xs={12} md={8}>
                    <Card>
                        <CardContent sx={{ p: 3 }}>
                            <Typography variant="h6" fontWeight={600} gutterBottom>Akses Cepat</Typography>
                            <Divider sx={{ mb: 2 }} />
                            <Grid container spacing={1.5}>
                                <Grid item xs={12} sm={6}>
                                    <QuickAction icon={<MeetingRoomIcon />} label="Booking Ruang Meeting" color="primary" onClick={() => navigate('/meeting-rooms/create')} />
                                </Grid>
                                <Grid item xs={12} sm={6}>
                                    <QuickAction icon={<DirectionsCarIcon />} label="Booking Kendaraan" color="success" onClick={() => navigate('/vehicle-bookings/create')} />
                                </Grid>
                                <Grid item xs={12} sm={6}>
                                    <QuickAction icon={<AddIcon />} label="Request ATK/Sparepart" color="info" onClick={() => navigate('/inventory-requests/create')} />
                                </Grid>
                                <Grid item xs={12} sm={6}>
                                    <QuickAction icon={<CalendarMonthIcon />} label="Jadwal Ruang Meeting" color="secondary" onClick={() => navigate('/meeting-rooms/calendar')} />
                                </Grid>
                            </Grid>
                        </CardContent>
                    </Card>
                </Grid>

                {/* Alerts / Low Stock - Bento block */}
                <Grid item xs={12} md={4}>
                    <Card sx={{ height: '100%' }}>
                        <CardContent sx={{ p: 3 }}>
                            <Typography variant="h6" fontWeight={600} gutterBottom>
                                <WarningAmberIcon sx={{ mr: 1, verticalAlign: 'middle', color: 'warning.main' }} />
                                Perhatian
                            </Typography>
                            <Divider sx={{ mb: 2 }} />
                            {stats.low_stock_items > 0 ? (
                                <Box>
                                    <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                                        {stats.low_stock_items} item inventaris stok rendah
                                    </Typography>
                                    <Button size="small" onClick={() => navigate('/inventory?low_stock=1')}>Lihat Detail</Button>
                                </Box>
                            ) : (
                                <Typography variant="body2" color="text.secondary">Tidak ada peringatan saat ini</Typography>
                            )}
                        </CardContent>
                    </Card>
                </Grid>

                {/* Recent Approvals */}
                {pendingApprovals.length > 0 && (
                    <Grid item xs={12}>
                        <Card>
                            <CardContent sx={{ p: 3 }}>
                                <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
                                    <Typography variant="h6" fontWeight={600}>Approval Terbaru</Typography>
                                    <Button size="small" onClick={() => navigate('/approvals')}>Lihat Semua</Button>
                                </Box>
                                <Divider sx={{ mb: 2 }} />
                                <Grid container spacing={1.5}>
                                    {pendingApprovals.slice(0, 4).map((approval) => (
                                        <Grid item xs={12} sm={6} md={3} key={approval.id}>
                                            <Card variant="outlined" sx={{ cursor: 'pointer' }} onClick={() => navigate('/approvals')}>
                                                <CardContent sx={{ py: 2, px: 2, '&:last-child': { pb: 2 } }}>
                                                    <Typography variant="body2" fontWeight={600} noWrap>
                                                        {approval.requestable_type?.split('\\').pop()} #{approval.requestable_id}
                                                    </Typography>
                                                    <Typography variant="caption" color="text.secondary">
                                                        Step {approval.current_step} dari {approval.step_instances?.length || '?'}
                                                    </Typography>
                                                </CardContent>
                                            </Card>
                                        </Grid>
                                    ))}
                                </Grid>
                            </CardContent>
                        </Card>
                    </Grid>
                )}
            </Grid>
        </Box>
    );
}

export default Dashboard;