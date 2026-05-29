import { configureStore } from '@reduxjs/toolkit';
import approvalReducer from './slices/approvalSlice';
import authReducer from './slices/authSlice';
import inventoryReducer from './slices/inventorySlice';
import meetingRoomReducer from './slices/meetingRoomSlice';
import uiReducer from './slices/uiSlice';
import userReducer from './slices/userSlice';
import vehicleReducer from './slices/vehicleSlice';

const store = configureStore({
    reducer: {
        auth: authReducer,
        ui: uiReducer,
        vehicles: vehicleReducer,
        inventory: inventoryReducer,
        approvals: approvalReducer,
        meetingRooms: meetingRoomReducer,
        users: userReducer,
    },
});

export default store;