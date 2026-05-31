import AddIcon from '@mui/icons-material/Add';
import AssignmentTurnedInIcon from '@mui/icons-material/AssignmentTurnedIn';
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import Inventory2Icon from '@mui/icons-material/Inventory2';
import Avatar from '@mui/material/Avatar';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardActionArea from '@mui/material/CardActionArea';
import CardContent from '@mui/material/CardContent';
import Chip from '@mui/material/Chip';
import Divider from '@mui/material/Divider';
import Grid from '@mui/material/Grid';
import Typography from '@mui/material/Typography';
import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Link } from 'react-router-dom';
import { fetchPendingApprovals } from '../store/slices/approvalSlice';
import { fetchItems } from '../store/slices/inventorySlice';
import { fetchVehicles } from '../store/slices/vehicleSlice';

function StatCard({ icon, title, value, color, onClick }) {
  return (
    <Card elevation={1} sx={{ borderRadius: 2 }}>
      <CardActionArea onClick={onClick} sx={{ p: 2.5 }}>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
          <Avatar sx={{ bgcolor: `${color}.light`, color: `${color}.main`, width: 52, height: 52 }}>{icon}</Avatar>
          <Box>
            <Typography variant="h4" fontWeight={700}>{value ?? 0}</Typography>
            <Typography variant="body2" color="text.secondary" fontWeight={500}>{title}</Typography>
          </Box>
        </Box>
      </CardActionArea>
    </Card>
  );
}

export default function Home() {
  const dispatch = useDispatch();
  const user = useSelector((s) => s.auth.user);
  const { pendingApprovals } = useSelector((s) => s.approvals);
  const { vehicles } = useSelector((s) => s.vehicles);
  const { items } = useSelector((s) => s.inventory);

  useEffect(() => {
    dispatch(fetchPendingApprovals());
    dispatch(fetchVehicles());
    dispatch(fetchItems());
  }, [dispatch]);

  const greetingTime = () => {
    const h = new Date().getHours();
    if (h < 12) return 'Selamat Pagi';
    if (h < 17) return 'Selamat Siang';
    return 'Selamat Malam';
  };

  return (
    <Box>
      {/* Greeting */}
      <Box sx={{ mb: 4 }}>
        <Typography variant="h4" fontWeight={700}>
          {greetingTime()}, {user?.name?.split(' ')[0] || 'User'} 👋
        </Typography>
        <Typography variant="body1" color="text.secondary" sx={{ mt: 0.5 }}>
          ITApp — Integrated Management System
        </Typography>
      </Box>

      {/* Stats Grid */}
      <Grid container spacing={3} sx={{ mb: 4 }}>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            icon={<CalendarMonthIcon />}
            title="Pending Approvals"
            value={pendingApprovals?.length || 0}
            color="warning"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            icon={<DirectionsCarIcon />}
            title="Vehicles"
            value={vehicles?.length || 0}
            color="info"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            icon={<Inventory2Icon />}
            title="Inventory Items"
            value={items?.length || (Array.isArray(items) ? items.length : 0)}
            color="success"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            icon={<AssignmentTurnedInIcon />}
            title="Approvals"
            value={pendingApprovals?.length || 0}
            color="primary"
          />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        {/* Quick Actions */}
        <Grid item xs={12} md={4}>
          <Card elevation={1} sx={{ borderRadius: 2 }}>
            <CardContent>
              <Typography variant="h6" fontWeight={600} gutterBottom>Quick Actions</Typography>
              <Divider sx={{ mb: 2 }} />
              <Box sx={{ display: 'flex', flexDirection: 'column', gap: 1 }}>
                <Button component={Link} to="/meeting-rooms/create" variant="outlined" startIcon={<AddIcon />} fullWidth>
                  Book Meeting Room
                </Button>
                <Button component={Link} to="/vehicle-bookings/create" variant="outlined" startIcon={<AddIcon />} fullWidth>
                  Book Vehicle
                </Button>
                <Button component={Link} to="/inventory-requests/create" variant="outlined" startIcon={<AddIcon />} fullWidth>
                  Inventory Request
                </Button>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        {/* Pending Approvals */}
        <Grid item xs={12} md={8}>
          <Card elevation={1} sx={{ borderRadius: 2 }}>
            <CardContent>
              <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1 }}>
                <Typography variant="h6" fontWeight={600}>Pending Approvals</Typography>
                <Button component={Link} to="/approvals" size="small" color="primary">View All</Button>
              </Box>
              <Divider sx={{ mb: 2 }} />
              {pendingApprovals && pendingApprovals.length > 0 ? (
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 1 }}>
                  {pendingApprovals.slice(0, 5).map((approval, idx) => (
                    <Box key={approval.id || idx} sx={{ p: 1.5, border: '1px solid', borderColor: 'divider', borderRadius: 1.5, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <Box>
                        <Typography variant="subtitle2">{approval.module || approval.type || 'Request'}</Typography>
                        <Typography variant="caption" color="text.secondary">
                          {approval.description || approval.user?.name || ''}
                        </Typography>
                      </Box>
                      <Chip label={approval.status || 'pending'} size="small" color="warning" variant="outlined" />
                    </Box>
                  ))}
                </Box>
              ) : (
                <Typography variant="body2" color="text.secondary" sx={{ py: 3, textAlign: 'center' }}>
                  Tidak ada approval pending
                </Typography>
              )}
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Box>
  );
}
