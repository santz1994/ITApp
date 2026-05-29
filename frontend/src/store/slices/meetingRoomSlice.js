import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import api from '../../services/api';

export const fetchMeetingRoomBookings = createAsyncThunk('meetingRooms/fetchAll', async (params = {}, { rejectWithValue }) => {
  try {
    const response = await api.get('/meeting-room-bookings', { params });
    return response.data;
  } catch (error) {
    return rejectWithValue(error.response?.data?.message || 'Gagal memuat data booking ruang meeting');
  }
});

export const fetchMeetingRoomBooking = createAsyncThunk('meetingRooms/fetchOne', async (id, { rejectWithValue }) => {
  try {
    const response = await api.get(`/meeting-room-bookings/${id}`);
    return response.data.data;
  } catch (error) {
    return rejectWithValue(error.response?.data?.message || 'Gagal memuat data booking');
  }
});

export const createMeetingRoomBooking = createAsyncThunk('meetingRooms/create', async (data, { rejectWithValue }) => {
  try {
    const response = await api.post('/meeting-room-bookings', data);
    return response.data.data;
  } catch (error) {
    return rejectWithValue(error.response?.data?.message || 'Gagal membuat booking');
  }
});

export const approveMeetingRoomBooking = createAsyncThunk('meetingRooms/approve', async (id, { rejectWithValue }) => {
  try {
    const response = await api.post(`/meeting-room-bookings/${id}/approve`);
    return response.data.data;
  } catch (error) {
    return rejectWithValue(error.response?.data?.message || 'Gagal menyetujui booking');
  }
});

export const rejectMeetingRoomBooking = createAsyncThunk('meetingRooms/reject', async ({ id, reason }, { rejectWithValue }) => {
  try {
    const response = await api.post(`/meeting-room-bookings/${id}/reject`, { rejection_reason: reason });
    return response.data.data;
  } catch (error) {
    return rejectWithValue(error.response?.data?.message || 'Gagal menolak booking');
  }
});

const meetingRoomSlice = createSlice({
  name: 'meetingRooms',
  initialState: {
    bookings: [],
    currentBooking: null,
    stats: {},
    loading: false,
    error: null,
  },
  reducers: {
    clearError: (state) => { state.error = null; },
    clearCurrentBooking: (state) => { state.currentBooking = null; },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchMeetingRoomBookings.pending, (state) => { state.loading = true; state.error = null; })
      .addCase(fetchMeetingRoomBookings.fulfilled, (state, action) => {
        state.loading = false;
        state.bookings = action.payload.data || [];
        state.stats = action.payload.stats || {};
      })
      .addCase(fetchMeetingRoomBookings.rejected, (state, action) => { state.loading = false; state.error = action.payload; })
      .addCase(fetchMeetingRoomBooking.pending, (state) => { state.loading = true; })
      .addCase(fetchMeetingRoomBooking.fulfilled, (state, action) => { state.loading = false; state.currentBooking = action.payload; })
      .addCase(fetchMeetingRoomBooking.rejected, (state, action) => { state.loading = false; state.error = action.payload; })
      .addCase(createMeetingRoomBooking.fulfilled, (state, action) => { state.bookings.unshift(action.payload); })
      .addCase(approveMeetingRoomBooking.fulfilled, (state, action) => {
        const idx = state.bookings.findIndex(b => b.id === action.payload?.id);
        if (idx !== -1) state.bookings[idx] = action.payload;
        if (state.currentBooking?.id === action.payload?.id) state.currentBooking = action.payload;
      })
      .addCase(rejectMeetingRoomBooking.fulfilled, (state, action) => {
        const idx = state.bookings.findIndex(b => b.id === action.payload?.id);
        if (idx !== -1) state.bookings[idx] = action.payload;
        if (state.currentBooking?.id === action.payload?.id) state.currentBooking = action.payload;
      });
  },
});

export const { clearError, clearCurrentBooking } = meetingRoomSlice.actions;
export default meetingRoomSlice.reducer;
