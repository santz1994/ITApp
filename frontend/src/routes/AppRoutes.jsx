import Box from '@mui/material/Box';
import CircularProgress from '@mui/material/CircularProgress';
import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Navigate, Route, Routes } from 'react-router-dom';
import MainLayout from '../layouts/MainLayout';
import { fetchPendingApprovals } from '../store/slices/approvalSlice';
import { fetchUser } from '../store/slices/authSlice';

// Auth pages
import Login from '../pages/Login';

// Meeting Rooms
import LcdDashboard from '../pages/meeting-rooms/LcdDashboard';
import LcdMaintenance from '../pages/meeting-rooms/LcdMaintenance';
import LcdSettings from '../pages/meeting-rooms/LcdSettings';
import MeetingRoomCalendar from '../pages/meeting-rooms/MeetingRoomCalendar';
import MeetingRoomCreate from '../pages/meeting-rooms/MeetingRoomCreate';
import MeetingRoomDDashboard from '../pages/meeting-rooms/MeetingRoomDDashboard';
import MeetingRoomPrint from '../pages/meeting-rooms/MeetingRoomPrint';
import MeetingRoomRDashboard from '../pages/meeting-rooms/MeetingRoomRDashboard';
import MeetingRoomShow from '../pages/meeting-rooms/MeetingRoomShow';
import MeetingRoomsList from '../pages/MeetingRooms/MeetingRoomsList';

// Vehicles
import VehicleBookingCreate from '../pages/vehicles/VehicleBookingCreate';
import VehicleBookingShow from '../pages/vehicles/VehicleBookingShow';
import VehicleCreate from '../pages/vehicles/VehicleCreate';
import VehicleList from '../pages/vehicles/VehicleList';
import VehicleMyBookings from '../pages/vehicles/VehicleMyBookings';
import VehicleShow from '../pages/vehicles/VehicleShow';

// Inventory
import InventoryCreate from '../pages/inventory/InventoryCreate';
import InventoryList from '../pages/inventory/InventoryList';
import InventoryRequestCreate from '../pages/inventory/InventoryRequestCreate';
import InventoryRequests from '../pages/inventory/InventoryRequests';
import InventoryShow from '../pages/inventory/InventoryShow';

// Approvals
import ApprovalsShow from '../pages/approvals/ApprovalShow';
import PendingApprovals from '../pages/approvals/PendingApprovals';

// Users
import RoleManagement from '../pages/users/RoleManagement';
import UserCreate from '../pages/users/UserCreate';
import UserList from '../pages/users/UserList';
import UserShow from '../pages/users/UserShow';

// Profile
import ChangePassword from '../pages/profile/ChangePassword';
import ChangePicture from '../pages/profile/ChangePicture';
import Notifications from '../pages/profile/Notifications';
import ProfileEdit from '../pages/profile/ProfileEdit';

// Admin
import AdminDashboard from '../pages/admin/AdminDashboard';
import MenuCreate from '../pages/admin/menus/MenuCreate';
import MenuEdit from '../pages/admin/menus/MenuEdit';
import MenuIndex from '../pages/admin/menus/MenuIndex';
import MenuPermissions from '../pages/admin/menus/MenuPermissions';
import AdminNotificationSettings from '../pages/admin/NotificationSettings';

// Audit Logs
import AuditLogShow from '../pages/audit-logs/AuditLogShow';
import AuditLogsList from '../pages/audit-logs/AuditLogsList';

// Dashboard
import Dashboard from '../pages/Dashboard';

function RequireAuth({ children }) {
    const { token, isAuthenticated, loading } = useSelector((s) => s.auth);
    const hasAuthToken = Boolean(localStorage.getItem('auth_token'));

    // Still verifying auth → show spinner (not login)
    if (loading && hasAuthToken) {
        return (
            <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh' }}>
                <CircularProgress />
            </Box>
        );
    }

    // Auth verified → show content
    if (isAuthenticated) return children;

    // Not authenticated → redirect to login
    return <Navigate to="/login" replace />;
}

export default function AppRoutes() {
    const dispatch = useDispatch();
    const token = useSelector((s) => s.auth.token);
    const hasAuthToken = Boolean(localStorage.getItem('auth_token'));

    useEffect(() => {
        if (hasAuthToken && !token) {
            dispatch(fetchUser());
            dispatch(fetchPendingApprovals());
        }
    }, [dispatch, hasAuthToken, token]);

    return (
        <Routes>
            {/* Public */}
            <Route path="/login" element={<Login />} />
            <Route path="/meeting-room-lcd-dashboard" element={<LcdDashboard />} />

            {/* Protected */}
            <Route path="/" element={<RequireAuth><MainLayout /></RequireAuth>}>
                <Route index element={<Navigate to="/home" replace />} />
                <Route path="home" element={<Dashboard />} />

                {/* Meeting Rooms */}
                <Route path="meeting-rooms" element={<MeetingRoomsList />} />
                <Route path="meeting-rooms/create" element={<MeetingRoomCreate />} />
                <Route path="meeting-rooms/calendar" element={<MeetingRoomCalendar />} />
                <Route path="meeting-rooms/schedule" element={<ReceptionistSchedule />} />
                <Route path="meeting-rooms/r-dashboard" element={<MeetingRoomRDashboard />} />
                <Route path="meeting-rooms/d-dashboard" element={<MeetingRoomDDashboard />} />
                <Route path="meeting-rooms/lcd" element={<LcdDashboard />} />
                <Route path="meeting-rooms/lcd-settings" element={<LcdSettings />} />
                <Route path="meeting-rooms/lcd-maintenance" element={<LcdMaintenance />} />
                <Route path="meeting-rooms/rooms" element={<RoomManager />} />
                <Route path="meeting-rooms/:id" element={<MeetingRoomShow />} />
                <Route path="meeting-rooms/:id/print" element={<MeetingRoomPrint />} />
                <Route path="meeting-rooms/:id/edit" element={<MeetingRoomCreate />} />

                {/* Vehicles */}
                <Route path="vehicles" element={<VehicleList />} />
                <Route path="vehicles/create" element={<VehicleCreate />} />
                <Route path="vehicles/:id" element={<VehicleShow />} />
                <Route path="vehicles/:id/edit" element={<VehicleCreate />} />
                <Route path="vehicle-bookings/create" element={<VehicleBookingCreate />} />
                <Route path="vehicle-bookings/:id" element={<VehicleBookingShow />} />
                <Route path="my-bookings" element={<VehicleMyBookings />} />

                {/* Inventory */}
                <Route path="inventory" element={<InventoryList />} />
                <Route path="inventory/create" element={<InventoryCreate />} />
                <Route path="inventory/:id" element={<InventoryShow />} />
                <Route path="inventory/:id/edit" element={<InventoryCreate />} />
                <Route path="inventory-requests" element={<InventoryRequests />} />
                <Route path="inventory-requests/create" element={<InventoryRequestCreate />} />

                {/* Approvals */}
                <Route path="approvals" element={<PendingApprovals />} />
                <Route path="approvals/:id" element={<ApprovalsShow />} />

                {/* Users */}
                <Route path="users" element={<UserList />} />
                <Route path="users/create" element={<UserCreate />} />
                <Route path="users/:id" element={<UserShow />} />
                <Route path="users/:id/edit" element={<UserCreate />} />
                <Route path="users/roles" element={<RoleManagement />} />

                {/* Profile */}
                <Route path="profile" element={<ProfileEdit />} />
                <Route path="profile/change-password" element={<ChangePassword />} />
                <Route path="profile/change-picture" element={<ChangePicture />} />
                <Route path="profile/notifications" element={<Notifications />} />

                {/* Admin */}
                <Route path="admin" element={<AdminDashboard />} />
                <Route path="admin/menus" element={<MenuIndex />} />
                <Route path="admin/menus/create" element={<MenuCreate />} />
                <Route path="admin/menus/:id/edit" element={<MenuEdit />} />
                <Route path="admin/menus/:id/permissions" element={<MenuPermissions />} />
                <Route path="admin/notification-settings" element={<AdminNotificationSettings />} />
                <Route path="admin/system-settings" element={<SystemSettingsPage />} />

                {/* Audit Logs */}
                <Route path="audit-logs" element={<AuditLogsList />} />
                <Route path="audit-logs/:id" element={<AuditLogShow />} />

                {/* Catch-all */}
                <Route path="*" element={<Navigate to="/home" replace />} />
            </Route>
        </Routes>
    );
}
