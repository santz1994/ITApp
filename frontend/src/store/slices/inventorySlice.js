import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { inventoryApi } from '../../services/api';

export const fetchItems = createAsyncThunk('inventory/fetchAll', async (params = {}, { rejectWithValue }) => {
    try {
        const response = await inventoryApi.getAll(params);
        return response.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal memuat data inventaris');
    }
});

export const fetchItem = createAsyncThunk('inventory/fetchOne', async (id, { rejectWithValue }) => {
    try {
        const response = await inventoryApi.getById(id);
        return response.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal memuat data item');
    }
});

export const createItem = createAsyncThunk('inventory/create', async (data, { rejectWithValue }) => {
    try {
        const response = await inventoryApi.create(data);
        return response.data.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal menambah item');
    }
});

export const updateItem = createAsyncThunk('inventory/update', async ({ id, data }, { rejectWithValue }) => {
    try {
        const response = await inventoryApi.update(id, data);
        return response.data.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal memperbarui item');
    }
});

export const fetchRequests = createAsyncThunk('inventory/fetchRequests', async (params = {}, { rejectWithValue }) => {
    try {
        const response = await inventoryApi.getRequests(params);
        return response.data.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal memuat data request');
    }
});

export const fetchLowStock = createAsyncThunk('inventory/fetchLowStock', async (_, { rejectWithValue }) => {
    try {
        const response = await inventoryApi.getLowStock();
        return response.data.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal memuat data low stock');
    }
});

const inventorySlice = createSlice({
    name: 'inventory',
    initialState: {
        items: [],
        categories: [],
        stats: {},
        currentItem: null,
        requests: [],
        lowStockItems: [],
        stockMovements: [],
        loading: false,
        error: null,
    },
    reducers: {
        clearError: (state) => { state.error = null; },
        clearCurrentItem: (state) => { state.currentItem = null; },
    },
    extraReducers: (builder) => {
        builder
            .addCase(fetchItems.pending, (state) => { state.loading = true; state.error = null; })
            .addCase(fetchItems.fulfilled, (state, action) => {
                state.loading = false;
                state.items = action.payload.data;
                state.categories = action.payload.categories;
                state.stats = action.payload.stats;
            })
            .addCase(fetchItems.rejected, (state, action) => { state.loading = false; state.error = action.payload; })
            .addCase(fetchItem.pending, (state) => { state.loading = true; })
            .addCase(fetchItem.fulfilled, (state, action) => {
                state.loading = false;
                state.currentItem = action.payload.data;
                state.stockMovements = action.payload.stock_movements;
            })
            .addCase(fetchItem.rejected, (state, action) => { state.loading = false; state.error = action.payload; })
            .addCase(createItem.fulfilled, (state, action) => { state.items.push(action.payload); })
            .addCase(updateItem.fulfilled, (state, action) => {
                const index = state.items.findIndex(i => i.id === action.payload.id);
                if (index !== -1) state.items[index] = action.payload;
                state.currentItem = action.payload;
            })
            .addCase(fetchRequests.fulfilled, (state, action) => { state.requests = action.payload; })
            .addCase(fetchLowStock.fulfilled, (state, action) => { state.lowStockItems = action.payload; });
    },
});

export const { clearError, clearCurrentItem } = inventorySlice.actions;
export default inventorySlice.reducer;