import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { approvalApi } from '../../services/api';

export const fetchPendingApprovals = createAsyncThunk('approvals/fetchPending', async (_, { rejectWithValue }) => {
    try {
        const response = await approvalApi.getPending();
        return response.data.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal memuat data approval');
    }
});

export const approveRequest = createAsyncThunk('approvals/approve', async ({ id, comments }, { rejectWithValue }) => {
    try {
        const response = await approvalApi.approve(id, { comments });
        return response.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal menyetujui request');
    }
});

export const rejectRequest = createAsyncThunk('approvals/reject', async ({ id, comments }, { rejectWithValue }) => {
    try {
        const response = await approvalApi.reject(id, { comments });
        return response.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal menolak request');
    }
});

export const fetchRules = createAsyncThunk('approvals/fetchRules', async (_, { rejectWithValue }) => {
    try {
        const response = await approvalApi.getRules();
        return response.data.data;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Gagal memuat approval rules');
    }
});

const approvalSlice = createSlice({
    name: 'approvals',
    initialState: {
        pendingApprovals: [],
        rules: [],
        loading: false,
        error: null,
    },
    reducers: {
        clearError: (state) => { state.error = null; },
    },
    extraReducers: (builder) => {
        builder
            .addCase(fetchPendingApprovals.pending, (state) => { state.loading = true; state.error = null; })
            .addCase(fetchPendingApprovals.fulfilled, (state, action) => { state.loading = false; state.pendingApprovals = action.payload; })
            .addCase(fetchPendingApprovals.rejected, (state, action) => { state.loading = false; state.error = action.payload; })
            .addCase(approveRequest.fulfilled, (state, action) => {
                state.pendingApprovals = state.pendingApprovals.filter(a => a.id !== action.payload.data?.id);
            })
            .addCase(rejectRequest.fulfilled, (state, action) => {
                state.pendingApprovals = state.pendingApprovals.filter(a => a.id !== action.payload.data?.id);
            })
            .addCase(fetchRules.fulfilled, (state, action) => { state.rules = action.payload; });
    },
});

export const { clearError } = approvalSlice.actions;
export default approvalSlice.reducer;