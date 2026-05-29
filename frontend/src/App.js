import { useEffect } from 'react';
import { useDispatch } from 'react-redux';
import { Navigate, Route, BrowserRouter as Router, Routes } from 'react-router-dom';
import { fetchPendingApprovals } from './store/slices/approvalSlice';
import { fetchUser } from './store/slices/authSlice';

// Layout
import AppLayout from './components/Layout/AppLayout';
import ProtectedRoute from './components/ProtectedRoute';

// Auth
import Login from './pages/Login';

// Dashboard
import Dashboard from './pages/Dashboard';

// Meeting Rooms
import MeetingRoomCalendar from './pages/meeting-rooms/MeetingRoomCalendar';
import MeetingRoomCreate from './pages/meeting-rooms/MeetingRoomCreate';
import MeetingRoomList from './pages/meeting-rooms/MeetingRoomList';
import MeetingRoomShow from './pages/meeting-rooms/MeetingRoomShow';

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

// Users
import RoleManagement from './pages/users/RoleManagement';
import UserCreate from './pages/users/UserCreate';
import UserList from './pages/users/UserList';
import UserShow from './pages/users/UserShow';

// Profile
import ChangePassword from './pages/profile/ChangePassword';
import ProfileEdit from './pages/profile/ProfileEdit';

// Reports
import ReportsDashboard from './pages/reports/ReportsDashboard';

function App() {
    const dispatch = useDispatch();

    useEffect(() => {
        dispatch(fetchUser());
        dispatch(fetchPendingApprovals());
    }, [dispatch]);

    return (
        <Router>
            <Routes>
                <Route path="/login" element={<Login />} />
                <Route path="/" element={<ProtectedRoute><AppLayout /></ProtectedRoute>}>
                    <Route index element={<Dashboard />} />

                    {/* Meeting Rooms */}
                    <Route path="meeting-rooms" element={<MeetingRoomList />} />
                    <Route path="meeting-rooms/create" element={<MeetingRoomCreate />} />
                    <Route path="meeting-rooms/calendar" element={<MeetingRoomCalendar />} />
                    <Route path="meeting-rooms/:id" element={<MeetingRoomShow />} />
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

                    {/* Users */}
                    <Route path="users" element={<UserList />} />
                    <Route path="users/create" element={<UserCreate />} />
                    <Route path="users/roles" element={<RoleManagement />} />
                    <Route path="users/:id" element={<UserShow />} />
                    <Route path="users/:id/edit" element={<UserCreate />} />

                    {/* Profile */}
                    <Route path="profile" element={<ProfileEdit />} />
                    <Route path="profile/change-password" element={<ChangePassword />} />

                    {/* Reports */}
                    <Route path="reports" element={<ReportsDashboard />} />
                </Route>
                <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
        </Router>
    );
}

export default App;