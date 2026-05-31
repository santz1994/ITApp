import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { authApi } from '../../services/api';

export const fetchUser = createAsyncThunk('auth/fetchUser', async (_, { rejectWithValue }) => {
    try {
        if (!localStorage.getItem('auth_token')) {
            return rejectWithValue('Not authenticated');
        }
        const response = await authApi.getUser();
        // Backend /api/user (Sanctum) returns user directly
        // Backend /api/user (wrapped) returns { success, data: { user } }
        const userData = response.data?.user || response.data?.data?.user || response.data;
        return userData;
    } catch (error) {
        return rejectWithValue('Not authenticated');
    }
});

export const login = createAsyncThunk('auth/login', async (credentials, { rejectWithValue }) => {
    try {
        const response = await authApi.login(credentials);
        const token = response.data?.token || response.data?.data?.token;
        const user = response.data?.user || response.data?.data?.user;
        if (token) {
            localStorage.setItem('auth_token', token);
        }
        return user;
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
    initialState: {
        user: null,
        isAuthenticated: false,
        // Start loading if token exists in localStorage (prevents Login flash on refresh)
        loading: Boolean(localStorage.getItem('auth_token')),
        error: null,
    },
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