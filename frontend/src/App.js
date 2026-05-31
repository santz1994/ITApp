import { useEffect } from 'react';
import { useDispatch } from 'react-redux';
import { Navigate, Route, Routes } from 'react-router-dom';
import { fetchPendingApprovals } from './store/slices/approvalSlice';
import { fetchUser } from './store/slices/authSlice';

// Layout
import AppLayout from './components/Layout/AppLayout';
import ProtectedRoute from './components/ProtectedRoute';

// Auth
import Login from './pages/Login';
import ForgotPassword from './pages/auth/ForgotPassword';
import Terms from './pages/auth/Terms';

// Dashboard
import Dashboard from './pages/Dashboard';
import Home from './pages/Home';

// Meeting Rooms
import MeetingRoomCalendar from './pages/meeting-rooms/MeetingRoomCalendar';
import MeetingRoomCreate from './pages/meeting-rooms/MeetingRoomCreate';
import MeetingRoomList from './pages/meeting-rooms/MeetingRoomList';
import MeetingRoomShow from './pages/meeting-rooms/MeetingRoomShow';
import MeetingRoomDDashboard from './pages/meeting-rooms/MeetingRoomDDashboard';
import MeetingRoomPrint from './pages/meeting-rooms/MeetingRoomPrint';
import MeetingRoomRDashboard from './pages/meeting-rooms/MeetingRoomRDashboard';
import LcdMaintenance from './pages/meeting-rooms/LcdMaintenance';

// Vehicles
import VehicleBookingCreate from './pages/vehicles/VehicleBookingCreate';
import VehicleBookingShow from './pages/vehicles/VehicleBookingShow';
import VehicleCreate from './pages/vehicles/VehicleCreate';
import VehicleList from './pages/vehicles/VehicleList';
import VehicleMyBookings from './pages/vehicles/VehicleMyBookings';
import VehicleShow from './pages/vehicles/VehicleShow';

// Inventory
import InventoryCreate from './pages/inventory/InventoryCreate';
import InventoryList from './pages/inventory/InventoryList';
import InventoryRequestCreate from './pages/inventory/InventoryRequestCreate';
import InventoryRequests from './pages/inventory/InventoryRequests';
import InventoryShow from './pages/inventory/InventoryShow';

// Approvals
import PendingApprovals from './pages/approvals/PendingApprovals';
import ApprovalsShow from './pages/approvals/ApprovalShow';

// Users
import RoleManagement from './pages/users/RoleManagement';
import UserCreate from './pages/users/UserCreate';
import UserList from './pages/users/UserList';
import UserShow from './pages/users/UserShow';

// Profile
import ChangePassword from './pages/profile/ChangePassword';
import ProfileEdit from './pages/profile/ProfileEdit';
import ChangePicture from './pages/profile/ChangePicture';
import Notifications from './pages/profile/Notifications';

// Auth extra
import Register from './pages/auth/Register';
import ResetPassword from './pages/auth/ResetPassword';

// Portal
import PortalIndex from './pages/portal/PortalIndex';

// Admin + Audit + LCD
import AdminDashboard from './pages/admin/AdminDashboard';
import MenuIndex from './pages/admin/menus/MenuIndex';
import MenuCreate from './pages/admin/menus/MenuCreate';
import MenuEdit from './pages/admin/menus/MenuEdit';
import MenuPermissions from './pages/admin/menus/MenuPermissions';
import AdminNotificationSettings from './pages/admin/NotificationSettings';
import AuditLogsList from './pages/audit-logs/AuditLogsList';
import AuditLogShow from './pages/audit-logs/AuditLogShow';
import LcdSettings from './pages/meeting-rooms/LcdSettings';
import LcdDashboard from './pages/meeting-rooms/LcdDashboard';

// Reports
import ReportsDashboard from './pages/reports/ReportsDashboard';

function App() {
    const dispatch = useDispatch();

    useEffect(() => {
        const token = localStorage.getItem('auth_token');

        if (token) {
            dispatch(fetchUser());
            dispatch(fetchPendingApprovals());
        }
    }, [dispatch]);

    return (
        <Routes>
                <Route path="/login" element={<Login />} />
            <Route path="/forgot-password" element={<ForgotPassword />} />
            <Route path="/terms" element={<Terms />} />
                <Route path="/" element={<ProtectedRoute><AppLayout /></ProtectedRoute>}>
                    <Route index element={<Dashboard />} />
                    <Route path="home" element={<Home />} />

                    {/* Meeting Rooms */}
                    <Route path="meeting-rooms" element={<MeetingRoomList />} />
                    <Route path="meeting-rooms/create" element={<MeetingRoomCreate />} />
                    <Route path="meeting-rooms/calendar" element={<MeetingRoomCalendar />} />
                    <Route path="meeting-rooms/r-dashboard" element={<MeetingRoomRDashboard />} />
                    <Route path="meeting-rooms/d-dashboard" element={<MeetingRoomDDashboard />} />
                    <Route path="meeting-rooms/lcd-maintenance" element={<LcdMaintenance />} />
                    <Route path="meeting-rooms/:id" element={<MeetingRoomShow />} />
                    <Route path="meeting-rooms/:id/print" element={<MeetingRoomPrint />} />
                    <Route path="meeting-rooms/:id/edit" element={<MeetingRoomCreate />} />

                    {/* Vehicles */}
                    <Route path="vehicles" element={<VehicleList />} />
                    <Route path="vehicles/create" element={<VehicleCreate />} />
                    <Route path="vehicles/:id" element={<VehicleShow />} />
                    <Route path="vehicles/:id/edit" element={<VehicleCreate />} />
                    <Route path="vehicle-bookings/create" element={<VehicleBookingCreate />} />
                    <Route path="vehicle-bookings/my" element={<VehicleMyBookings />} />
                    <Route path="vehicle-bookings/:id" element={<VehicleBookingShow />} />

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
                    <Route path="users/roles" element={<RoleManagement />} />
                    <Route path="users/:id" element={<UserShow />} />
                    <Route path="users/:id/edit" element={<UserCreate />} />

                    {/* Profile */}
                    <Route path="profile" element={<ProfileEdit />} />
                    <Route path="profile/change-password" element={<ChangePassword />} />
                    <Route path="profile/change-picture" element={<ChangePicture />} />
                    <Route path="profile/notifications" element={<Notifications />} />

                    {/* Reports */}
                    <Route path="reports" element={<ReportsDashboard />} />
                    {/* Admin */}
                    <Route path="admin" element={<AdminDashboard />} />
                    <Route path="admin/menus" element={<MenuIndex />} />
                    <Route path="admin/menus/create" element={<MenuCreate />} />
                    <Route path="admin/menus/:id/edit" element={<MenuEdit />} />
                    <Route path="admin/menus/permissions" element={<MenuPermissions />} />
                    <Route path="admin/notification-settings" element={<AdminNotificationSettings />} />
                    <Route path="audit-logs" element={<AuditLogsList />} />
                    <Route path="audit-logs/:id" element={<AuditLogShow />} />
                    <Route path="meeting-room-lcd-settings" element={<LcdSettings />} />
                    <Route path="meeting-room-lcd-dashboard" element={<LcdDashboard />} />
                    {/* Portal (public) */}
                    <Route path="portal" element={<PortalIndex />} />
                </Route>
                <Route path="/register" element={<Register />} />
                <Route path="/password/reset" element={<ResetPassword />} />
                <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
    );
}

export default App;