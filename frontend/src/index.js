import CssBaseline from '@mui/material/CssBaseline';
import { ThemeProvider } from '@mui/material/styles';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { Provider } from 'react-redux';
import { BrowserRouter } from 'react-router-dom';
import { setAuthToken } from './api/client';
import App from './App';
import store from './store';
import { setCredentials } from './store/authSlice';
import './styles.css';
import theme from './theme';

// Hydrate auth from localStorage
try {
    const saved = localStorage.getItem('itapp_auth');
    if (saved) {
        const parsed = JSON.parse(saved);
        if (parsed?.token) {
            setAuthToken(parsed.token);
            store.dispatch(setCredentials({ token: parsed.token, user: parsed.user || null }));
        }
    }
} catch (err) {
    // ignore
}

const container = document.getElementById('root');
const root = createRoot(container);

root.render(
    <React.StrictMode>
        <Provider store={store}>
            <ThemeProvider theme={theme}>
                <CssBaseline />
                <BrowserRouter>
                    <App />
                </BrowserRouter>
            </ThemeProvider>
        </Provider>
    </React.StrictMode>
);