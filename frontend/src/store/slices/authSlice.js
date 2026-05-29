import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { authApi } from '../../services/api';

export const fetchUser = createAsyncThunk('auth/fetchUser', async (_, { rejectWithValue }) => {
    try {
        const response = await authApi.getUser();
        return response.data;
    } catch (error) {
        return rejectWithValue('Not authenticated');
    }
});

export const login = createAsyncThunk('auth/login', async (credentials, { rejectWithValue }) => {
    try {
        const response = await authApi.login(credentials);
        if (response.data.token) {
            localStorage.setItem('auth_token', response.data.token);
        }
        return response.data.user;
    } catch (error) {
        return rejectWithValue(error.response?.data?.message || 'Login gagal');
    }
});

export const logout = createAsyncThunk('auth/logout', async () => {
    try { await authApi.logout(); } catch (e) { /* ignore */ }
    localStorage.removeItem('auth_token');
});

const authSlice = createSlice({
    name: 'auth',
    initialState: { user: null, isAuthenticated: false, loading: false, error: null },
    reducers: { clearAuthError: (state) => { state.error = null; } },
    extraReducers: (builder) => {
        builder
            .addCase(fetchUser.pending, (state) => { state.loading = true; })
            .addCase(fetchUser.fulfilled, (state, action) => {
                state.loading = false; state.user = action.payload; state.isAuthenticated = true;
            })
            .addCase(fetchUser.rejected, (state) => {
                state.loading = false; state.user = null; state.isAuthenticated = false;
            })
            .addCase(login.pending, (state) => { state.loading = true; state.error = null; })
            .addCase(login.fulfilled, (state, action) => {
                state.loading = false; state.user = action.payload; state.isAuthenticated = true;
            })
            .addCase(login.rejected, (state, action) => { state.loading = false; state.error = action.payload; })
            .addCase(logout.fulfilled, (state) => { state.user = null; state.isAuthenticated = false; });
    },
});

export const { clearAuthError } = authSlice.actions;
export default authSlice.reducer;