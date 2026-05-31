import Box from '@mui/material/Box';
import CircularProgress from '@mui/material/CircularProgress';
import Typography from '@mui/material/Typography';
import { useSelector } from 'react-redux';
import { Navigate } from 'react-router-dom';

function ProtectedRoute({ children, permission = null }) {
  const { isAuthenticated, loading, user } = useSelector((state) => state.auth);
  const hasAuthToken = Boolean(localStorage.getItem('auth_token'));

  if (loading || (!isAuthenticated && hasAuthToken)) {
    return (
      <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', minHeight: '100vh', gap: 2 }}>
        <CircularProgress size={48} />
        <Typography variant="body2" color="text.secondary">Memuat...</Typography>
      </Box>
    );
  }

  if (!isAuthenticated) return <Navigate to="/login" replace />;

  if (permission) {
    const userPermissions = user?.permissions || [];
    const userRoles = user?.roles?.map(r => r.name) || [];
    const hasAccess = userRoles.includes('developer') || userRoles.includes('administrator') || userPermissions.includes(permission);
    if (!hasAccess) return <Navigate to="/" replace />;
  }

  return children;
}

export default ProtectedRoute;
