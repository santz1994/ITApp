import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { vehicleApi } from '../../services/api';

export const fetchVehicles = createAsyncThunk('vehicles/fetchAll', async (params = {}, { rejectWithValue }) => {
    try {
        const response = await vehicleApi.getAll(params);
        return response.data.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal memuat data kendaraan');
    }
});

export const fetchVehicle = createAsyncThunk('vehicles/fetchOne', async (id, { rejectWithValue }) => {
    try {
        const response = await vehicleApi.getById(id);
        return response.data.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal memuat data kendaraan');
    }
});

export const createVehicle = createAsyncThunk('vehicles/create', async (data, { rejectWithValue }) => {
    try {
        const response = await vehicleApi.create(data);
        return response.data.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal menambah kendaraan');
    }
});

export const updateVehicle = createAsyncThunk('vehicles/update', async ({ id, data }, { rejectWithValue }) => {
    try {
        const response = await vehicleApi.update(id, data);
        return response.data.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal memperbarui kendaraan');
    }
});

export const deleteVehicle = createAsyncThunk('vehicles/delete', async (id, { rejectWithValue }) => {
    try {
        await vehicleApi.delete(id);
        return id;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal menghapus kendaraan');
    }
});

export const fetchBookings = createAsyncThunk('vehicles/fetchBookings', async (params = {}, { rejectWithValue }) => {
    try {
        const response = await vehicleApi.getBookings(params);
        return response.data.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal memuat data booking');
    }
});

export const createBooking = createAsyncThunk('vehicles/createBooking', async (data, { rejectWithValue }) => {
    try {
        const response = await vehicleApi.createBooking(data);
        return response.data.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal membuat booking');
    }
});

const vehicleSlice = createSlice({
    name: 'vehicles',
    initialState: {
        vehicles: [],
        currentVehicle: null,
        bookings: [],
        loading: false,
        error: null,
    },
    reducers: {
        clearError: (state) => { state.error = null; },
        clearCurrentVehicle: (state) => { state.currentVehicle = null; },
    },
    extraReducers: (builder) => {
        builder
            // Fetch all vehicles
            .addCase(fetchVehicles.pending, (state) => { state.loading = true; state.error = null; })
            .addCase(fetchVehicles.fulfilled, (state, action) => { state.loading = false; state.vehicles = action.payload; })
            .addCase(fetchVehicles.rejected, (state, action) => { state.loading = false; state.error = action.payload; })
            // Fetch single vehicle
            .addCase(fetchVehicle.pending, (state) => { state.loading = true; state.error = null; })
            .addCase(fetchVehicle.fulfilled, (state, action) => { state.loading = false; state.currentVehicle = action.payload; })
            .addCase(fetchVehicle.rejected, (state, action) => { state.loading = false; state.error = action.payload; })
            // Create vehicle
            .addCase(createVehicle.fulfilled, (state, action) => { state.vehicles.push(action.payload); })
            // Update vehicle
            .addCase(updateVehicle.fulfilled, (state, action) => {
                const index = state.vehicles.findIndex(v => v.id === action.payload.id);
                if (index !== -1) state.vehicles[index] = action.payload;
                state.currentVehicle = action.payload;
            })
            // Delete vehicle
            .addCase(deleteVehicle.fulfilled, (state, action) => {
                state.vehicles = state.vehicles.filter(v => v.id !== action.payload);
            })
            // Bookings
            .addCase(fetchBookings.pending, (state) => { state.loading = true; })
            .addCase(fetchBookings.fulfilled, (state, action) => { state.loading = false; state.bookings = action.payload; })
            .addCase(fetchBookings.rejected, (state, action) => { state.loading = false; state.error = action.payload; })
            .addCase(createBooking.fulfilled, (state, action) => { state.bookings.push(action.payload); });
    },
});

export const { clearError, clearCurrentVehicle } = vehicleSlice.actions;
export default vehicleSlice.reducer;