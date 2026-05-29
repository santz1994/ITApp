import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { vehicleApi } from '../../services/api';

export const fetchVehicles = createAsyncThunk('vehicles/fetchAll', async (params = {}, { rejectWithValue }) => {
    try { const res = await vehicleApi.getAll(params); return res.data.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memuat kendaraan'); }
});

export const fetchVehicle = createAsyncThunk('vehicles/fetchOne', async (id, { rejectWithValue }) => {
    try { const res = await vehicleApi.getById(id); return res.data.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memuat kendaraan'); }
});

export const createVehicle = createAsyncThunk('vehicles/create', async (data, { rejectWithValue }) => {
    try { const res = await vehicleApi.create(data); return res.data.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal menambah kendaraan'); }
});

export const updateVehicle = createAsyncThunk('vehicles/update', async ({ id, data }, { rejectWithValue }) => {
    try { const res = await vehicleApi.update(id, data); return res.data.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memperbarui kendaraan'); }
});

export const deleteVehicle = createAsyncThunk('vehicles/delete', async (id, { rejectWithValue }) => {
    try { await vehicleApi.delete(id); return id; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal menghapus kendaraan'); }
});

export const fetchMyBookings = createAsyncThunk('vehicles/fetchMyBookings', async (_, { rejectWithValue }) => {
    try { const res = await vehicleApi.getMyBookings(); return res.data.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memuat booking'); }
});

const vehicleSlice = createSlice({
    name: 'vehicles',
    initialState: { vehicles: [], currentVehicle: null, myBookings: [], loading: false, error: null },
    reducers: { clearError: (state) => { state.error = null; } },
    extraReducers: (builder) => {
        builder
            .addCase(fetchVehicles.pending, (s) => { s.loading = true; s.error = null; })
            .addCase(fetchVehicles.fulfilled, (s, a) => { s.loading = false; s.vehicles = a.payload; })
            .addCase(fetchVehicles.rejected, (s, a) => { s.loading = false; s.error = a.payload; })
            .addCase(fetchVehicle.fulfilled, (s, a) => { s.loading = false; s.currentVehicle = a.payload; })
            .addCase(createVehicle.fulfilled, (s, a) => { s.vehicles.unshift(a.payload); })
            .addCase(updateVehicle.fulfilled, (s, a) => {
                const i = s.vehicles.findIndex(v => v.id === a.payload.id);
                if (i !== -1) s.vehicles[i] = a.payload;
                s.currentVehicle = a.payload;
            })
            .addCase(deleteVehicle.fulfilled, (s, a) => { s.vehicles = s.vehicles.filter(v => v.id !== a.payload); })
            .addCase(fetchMyBookings.pending, (s) => { s.loading = true; })
            .addCase(fetchMyBookings.fulfilled, (s, a) => { s.loading = false; s.myBookings = a.payload; })
            .addCase(fetchMyBookings.rejected, (s, a) => { s.loading = false; s.error = a.payload; });
    },
});

export const { clearError } = vehicleSlice.actions;
export default vehicleSlice.reducer;