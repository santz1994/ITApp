import axios from 'axios';

// Use a relative API path so CRA can proxy requests in development.
// In production, Laravel serves the SPA from the same origin.
const defaultApiBase = '/api';

const api = axios.create({
    baseURL: process.env.REACT_APP_API_URL || defaultApiBase,
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    withCredentials: false,
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
}, (error) => Promise.reject(error));

api.interceptors.response.use((response) => response, (error) => {
    if (error.response?.status === 401) {
        localStorage.removeItem('auth_token');
        window.location.href = '/login';
    }
    return Promise.reject(error);
});

// ========================================
// AUTH API
// ========================================
export const authApi = {
    login: (credentials) => api.post('/login', credentials),
    logout: () => api.post('/logout'),
    getUser: () => api.get('/user'),
};

// ========================================
// VEHICLE API
// ========================================
export const vehicleApi = {
    getAll: (params) => api.get('/vehicles', { params }),
    getById: (id) => api.get(`/vehicles/${id}`),
    create: (data) => api.post('/vehicles', data),
    update: (id, data) => api.put(`/vehicles/${id}`, data),
    delete: (id) => api.delete(`/vehicles/${id}`),
    checkAvailability: (data) => api.post('/vehicles/check-availability', data),
    getBookings: (params) => api.get('/vehicles/bookings/all', { params }),
    getMyBookings: () => api.get('/vehicles/bookings/my'),
    createBooking: (data) => api.post('/vehicles/bookings', data),
    getBooking: (id) => api.get(`/vehicles/bookings/${id}`),
    approveBooking: (id, data) => api.post(`/vehicles/bookings/${id}/approve`, data),
    rejectBooking: (id, data) => api.post(`/vehicles/bookings/${id}/reject`, data),
    cancelBooking: (id) => api.post(`/vehicles/bookings/${id}/cancel`),
    startTrip: (id) => api.post(`/vehicles/bookings/${id}/start`),
    completeTrip: (id, data) => api.post(`/vehicles/bookings/${id}/complete`, data),
    getMaintenanceLogs: (id) => api.get(`/vehicles/${id}/maintenance`),
    addMaintenance: (id, data) => api.post(`/vehicles/${id}/maintenance`, data),
};

// ========================================
// INVENTORY API
// ========================================
export const inventoryApi = {
    getAll: (params) => api.get('/inventory', { params }),
    getById: (id) => api.get(`/inventory/${id}`),
    create: (data) => api.post('/inventory', data),
    update: (id, data) => api.put(`/inventory/${id}`, data),
    delete: (id) => api.delete(`/inventory/${id}`),
    addStock: (id, data) => api.post(`/inventory/${id}/add-stock`, data),
    reduceStock: (id, data) => api.post(`/inventory/${id}/reduce-stock`, data),
    getLowStock: () => api.get('/inventory/low-stock'),
    getCategories: () => api.get('/inventory/categories'),
    getRequests: (params) => api.get('/inventory/requests/all', { params }),
    createRequest: (data) => api.post('/inventory/requests', data),
    getRequest: (id) => api.get(`/inventory/requests/${id}`),
    approveRequest: (id) => api.post(`/inventory/requests/${id}/approve`),
    rejectRequest: (id, data) => api.post(`/inventory/requests/${id}/reject`, data),
    fulfillRequest: (id, data) => api.post(`/inventory/requests/${id}/fulfill`, data),
    cancelRequest: (id) => api.post(`/inventory/requests/${id}/cancel`),
};

// ========================================
// APPROVAL API
// ========================================
export const approvalApi = {
    getPending: () => api.get('/approvals/pending'),
    approve: (id, data) => api.post(`/approvals/${id}/approve`, data),
    reject: (id, data) => api.post(`/approvals/${id}/reject`, data),
    show: (id) => api.get(`/approvals/${id}`),
    getRules: () => api.get('/approvals/rules/all'),
    createRule: (data) => api.post('/approvals/rules', data),
    updateRule: (id, data) => api.put(`/approvals/rules/${id}`, data),
    deleteRule: (id) => api.delete(`/approvals/rules/${id}`),
    toggleRule: (id) => api.post(`/approvals/rules/${id}/toggle`),
};

// ========================================
// USER MANAGEMENT API
// ========================================
export const userApi = {
    getAll: (params) => api.get('/users', { params }),
    getById: (id) => api.get(`/users/${id}`),
    create: (data) => api.post('/users', data),
    update: (id, data) => api.put(`/users/${id}`, data),
    delete: (id) => api.delete(`/users/${id}`),
    bulkDelete: (ids) => api.post('/users/bulk-delete', { ids }),
    getRoles: () => api.get('/users/roles'),
};

// ========================================
// PROFILE API
// ========================================
export const profileApi = {
    get: () => api.get('/profile'),
    update: (data) => api.put('/profile', data),
    changePassword: (data) => api.put('/profile/change-password', data),
    uploadPicture: (formData) => api.post('/profile/change-picture', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    }),
    deletePicture: () => api.delete('/profile/delete-picture'),
    getNotifications: () => api.get('/profile/notifications'),
    updateNotifications: (data) => api.put('/profile/notifications', data),
};

// ========================================
// REPORT API
// ========================================
export const reportApi = {
    getDashboard: () => api.get('/reports/dashboard'),
    getMeetingRoomReport: (params) => api.get('/reports/meeting-rooms', { params }),
    getVehicleReport: (params) => api.get('/reports/vehicles', { params }),
    getInventoryReport: (params) => api.get('/reports/inventory', { params }),
};

// ========================================
// SYSTEM SETTINGS API
// ========================================
export const settingsApi = {
    getDivisions: () => api.get('/system-settings/divisions'),
    getMenus: () => api.get('/menus'),
};

export default api;

