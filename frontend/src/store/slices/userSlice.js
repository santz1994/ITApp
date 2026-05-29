import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { userApi } from '../../services/api';

export const fetchUsers = createAsyncThunk('users/fetchAll', async (params = {}, { rejectWithValue }) => {
  try { const res = await userApi.getAll(params); return res.data; }
  catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memuat users'); }
});

export const fetchUser = createAsyncThunk('users/fetchOne', async (id, { rejectWithValue }) => {
  try { const res = await userApi.getById(id); return res.data.data; }
  catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memuat user'); }
});

export const createUser = createAsyncThunk('users/create', async (data, { rejectWithValue }) => {
  try { const res = await userApi.create(data); return res.data.data; }
  catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal menambah user'); }
});

export const updateUser = createAsyncThunk('users/update', async ({ id, data }, { rejectWithValue }) => {
  try { const res = await userApi.update(id, data); return res.data.data; }
  catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memperbarui user'); }
});

export const deleteUser = createAsyncThunk('users/delete', async (id, { rejectWithValue }) => {
  try { await userApi.delete(id); return id; }
  catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal menghapus user'); }
});

export const fetchRoles = createAsyncThunk('users/fetchRoles', async (_, { rejectWithValue }) => {
  try { const res = await userApi.getRoles(); return res.data.data; }
  catch (e) { return rejectWithValue(e.response?.data?.message || 'Gagal memuat roles'); }
});

const userSlice = createSlice({
  name: 'users',
  initialState: { users: [], currentUser: null, roles: [], loading: false, error: null },
  reducers: { clearError: (s) => { s.error = null; } },
  extraReducers: (builder) => {
    builder
      .addCase(fetchUsers.pending, (s) => { s.loading = true; s.error = null; })
      .addCase(fetchUsers.fulfilled, (s, a) => { s.loading = false; s.users = a.payload.data || a.payload; })
      .addCase(fetchUsers.rejected, (s, a) => { s.loading = false; s.error = a.payload; })
      .addCase(fetchUser.fulfilled, (s, a) => { s.loading = false; s.currentUser = a.payload; })
      .addCase(createUser.fulfilled, (s, a) => { s.users.unshift(a.payload); })
      .addCase(updateUser.fulfilled, (s, a) => {
        const i = s.users.findIndex(u => u.id === a.payload.id);
        if (i !== -1) s.users[i] = a.payload; s.currentUser = a.payload;
      })
      .addCase(deleteUser.fulfilled, (s, a) => { s.users = s.users.filter(u => u.id !== a.payload); })
      .addCase(fetchRoles.fulfilled, (s, a) => { s.roles = a.payload; });
  },
});

export const { clearError } = userSlice.actions;
export default userSlice.reducer;
