import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Navigate, Route, BrowserRouter as Router, Routes } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { fetchUser } from './store/slices/authSlice';

// Layout
import Layout from './components/Layout/Layout';
import Login from './pages/Login';

// Pages
import PendingApprovals from './pages/approvals/PendingApprovals';
import Dashboard from './pages/Dashboard';
import InventoryCreate from './pages/inventory/InventoryCreate';
import InventoryList from './pages/inventory/InventoryList';
import InventoryRequestCreate from './pages/inventory/InventoryRequestCreate';
import InventoryRequests from './pages/inventory/InventoryRequests';
import InventoryShow from './pages/inventory/InventoryShow';
import VehicleBookingCreate from './pages/vehicles/VehicleBookingCreate';
import VehicleBookingShow from './pages/vehicles/VehicleBookingShow';
import VehicleCreate from './pages/vehicles/VehicleCreate';
import VehicleList from './pages/vehicles/VehicleList';
import VehicleMyBookings from './pages/vehicles/VehicleMyBookings';
import VehicleShow from './pages/vehicles/VehicleShow';

function ProtectedRoute({ children }) {
    const { isAuthenticated, loading } = useSelector((state) => state.auth);
    if (loading) return <div className="text-center p-5"><i className="fa fa-spinner fa-spin fa-3x"></i></div>;
    if (!isAuthenticated) return <Navigate to="/login" />;
    return children;
}

function App() {
    const dispatch = useDispatch();

    useEffect(() => {
        dispatch(fetchUser());
    }, [dispatch]);

    return (
        <Router>
            <ToastContainer position="top-right" autoClose={3000} />
            <Routes>
                <Route path="/login" element={<Login />} />
                <Route path="/" element={<ProtectedRoute><Layout /></ProtectedRoute>}>
                    <Route index element={<Dashboard />} />
                    <Route path="vehicles" element={<VehicleList />} />
                    <Route path="vehicles/create" element={<VehicleCreate />} />
                    <Route path="vehicles/:id" element={<VehicleShow />} />
                    <Route path="vehicles/:id/edit" element={<VehicleCreate />} />
                    <Route path="vehicle-bookings/create" element={<VehicleBookingCreate />} />
                    <Route path="vehicle-bookings/my" element={<VehicleMyBookings />} />
                    <Route path="vehicle-bookings/:id" element={<VehicleBookingShow />} />
                    <Route path="inventory" element={<InventoryList />} />
                    <Route path="inventory/create" element={<InventoryCreate />} />
                    <Route path="inventory/:id" element={<InventoryShow />} />
                    <Route path="inventory/:id/edit" element={<InventoryCreate />} />
                    <Route path="inventory-requests" element={<InventoryRequests />} />
                    <Route path="inventory-requests/create" element={<InventoryRequestCreate />} />
                    <Route path="approvals" element={<PendingApprovals />} />
                </Route>
                <Route path="*" element={<Navigate to="/" />} />
            </Routes>
        </Router>
    );
}

export default App;