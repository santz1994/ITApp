import { configureStore } from '@reduxjs/toolkit';
import approvalReducer from './slices/approvalSlice';
import authReducer from './slices/authSlice';
import inventoryReducer from './slices/inventorySlice';
import vehicleReducer from './slices/vehicleSlice';

const store = configureStore({
    reducer: {
        auth: authReducer,
        vehicles: vehicleReducer,
        inventory: inventoryReducer,
        approvals: approvalReducer,
    },
});

export default store;