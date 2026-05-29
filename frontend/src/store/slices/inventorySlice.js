import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { inventoryApi } from '../../services/api';

export const fetchItems = createAsyncThunk('inventory/fetchAll', async (params = {}, { rejectWithValue }) => {
    try { const res = await inventoryApi.getAll(params); return res.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memuat inventaris'); }
});

export const fetchItem = createAsyncThunk('inventory/fetchOne', async (id, { rejectWithValue }) => {
    try { const res = await inventoryApi.getById(id); return res.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memuat item'); }
});

export const createItem = createAsyncThunk('inventory/create', async (data, { rejectWithValue }) => {
    try { const res = await inventoryApi.create(data); return res.data.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal menambah item'); }
});

export const updateItem = createAsyncThunk('inventory/update', async ({ id, data }, { rejectWithValue }) => {
    try { const res = await inventoryApi.update(id, data); return res.data.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memperbarui item'); }
});

export const fetchRequests = createAsyncThunk('inventory/fetchRequests', async (params = {}, { rejectWithValue }) => {
    try { const res = await inventoryApi.getRequests(params); return res.data.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memuat request'); }
});

export const fetchLowStock = createAsyncThunk('inventory/fetchLowStock', async (_, { rejectWithValue }) => {
    try { const res = await inventoryApi.getLowStock(); return res.data.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memuat low stock'); }
});

const inventorySlice = createSlice({
    name: 'inventory',
    initialState: { items: [], categories: [], stats: {}, currentItem: null, requests: [], lowStockItems: [], stockMovements: [], loading: false, error: null },
    reducers: { clearError: (s) => { s.error = null; }, clearCurrentItem: (s) => { s.currentItem = null; } },
    extraReducers: (builder) => {
        builder
            .addCase(fetchItems.pending, (s) => { s.loading = true; s.error = null; })
            .addCase(fetchItems.fulfilled, (s, a) => {
                s.loading = false; s.items = a.payload.data || []; s.categories = a.payload.categories || []; s.stats = a.payload.stats || {};
            })
            .addCase(fetchItems.rejected, (s, a) => { s.loading = false; s.error = a.payload; })
            .addCase(fetchItem.fulfilled, (s, a) => {
                s.loading = false; s.currentItem = a.payload.data; s.stockMovements = a.payload.stock_movements || [];
            })
            .addCase(createItem.fulfilled, (s, a) => { s.items.unshift(a.payload); })
            .addCase(updateItem.fulfilled, (s, a) => {
                const i = s.items.findIndex(x => x.id === a.payload.id);
                if (i !== -1) s.items[i] = a.payload; s.currentItem = a.payload;
            })
            .addCase(fetchRequests.pending, (s) => { s.loading = true; })
            .addCase(fetchRequests.fulfilled, (s, a) => { s.loading = false; s.requests = a.payload; })
            .addCase(fetchRequests.rejected, (s, a) => { s.loading = false; s.error = a.payload; })
            .addCase(fetchLowStock.fulfilled, (s, a) => { s.lowStockItems = a.payload; });
    },
});

export const { clearError, clearCurrentItem } = inventorySlice.actions;
export default inventorySlice.reducer;