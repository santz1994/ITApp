import { useSelector } from 'react-redux';
import { Navigate, Route, Routes } from 'react-router-dom';
import MainLayout from '../layouts/MainLayout';
import Login from '../pages/Login';
import MeetingRoomsCalendar from '../pages/MeetingRooms/MeetingRoomsCalendar';
import MeetingRoomsList from '../pages/MeetingRooms/MeetingRoomsList';

function RequireAuth({ children }) {
    const token = useSelector((s) => s.auth.token);
    return token ? children : <Navigate to="/login" replace />;
}

export default function AppRoutes() {
    return (
        <Routes>
            <Route path="/login" element={<Login />} />
            <Route path="/" element={<RequireAuth><MainLayout /></RequireAuth>}>
                <Route index element={<Navigate to="/meeting-rooms" replace />} />
                <Route path="meeting-rooms" element={<MeetingRoomsList />} />
                <Route path="meeting-rooms/calendar" element={<MeetingRoomsCalendar />} />
            </Route>
        </Routes>
    );
}
