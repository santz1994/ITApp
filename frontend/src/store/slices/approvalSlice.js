import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { approvalApi } from '../../services/api';

export const fetchPendingApprovals = createAsyncThunk('approvals/fetchPending', async (_, { rejectWithValue }) => {
    try { const res = await approvalApi.getPending(); return res.data.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memuat approval'); }
});

export const approveRequest = createAsyncThunk('approvals/approve', async ({ id, comments }, { rejectWithValue }) => {
    try { const res = await approvalApi.approve(id, { comments }); return res.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal menyetujui'); }
});

export const rejectRequest = createAsyncThunk('approvals/reject', async ({ id, comments }, { rejectWithValue }) => {
    try { const res = await approvalApi.reject(id, { comments }); return res.data; }
    catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal menolak'); }
});

const approvalSlice = createSlice({
    name: 'approvals',
    initialState: { pendingApprovals: [], rules: [], loading: false, error: null },
    reducers: { clearError: (s) => { s.error = null; } },
    extraReducers: (builder) => {
        builder
            .addCase(fetchPendingApprovals.pending, (s) => { s.loading = true; s.error = null; })
            .addCase(fetchPendingApprovals.fulfilled, (s, a) => { s.loading = false; s.pendingApprovals = a.payload; })
            .addCase(fetchPendingApprovals.rejected, (s, a) => { s.loading = false; s.error = a.payload; })
            .addCase(approveRequest.fulfilled, (s, a) => {
                s.pendingApprovals = s.pendingApprovals.filter(x => x.id !== a.payload.data?.id);
            })
            .addCase(rejectRequest.fulfilled, (s, a) => {
                s.pendingApprovals = s.pendingApprovals.filter(x => x.id !== a.payload.data?.id);
            });
    },
});

export const { clearError } = approvalSlice.actions;
export default approvalSlice.reducer;