# Frontend, Mobile & Desktop Applications Architecture

**Proyek:** ITQuty Multi-Platform Applications  
**Platform:** Web, iOS, Android, Windows, macOS, Linux  
**Tanggal:** 18 Desember 2025

---

## 🎯 Overview

Dengan microservices backend, kita bisa build **4 aplikasi berbeda** yang semua consume API yang sama:

```
┌─────────────────────────────────────────────────────────────┐
│                    BACKEND (API)                             │
│  Microservices dengan REST/GraphQL API                       │
│  http://api.itquty.com                                       │
└────────────────┬────────────────────────────────────────────┘
                 │
     ┌───────────┴───────────┬───────────┬──────────────┐
     │                       │           │              │
     ▼                       ▼           ▼              ▼
┌──────────┐         ┌──────────┐  ┌─────────┐  ┌─────────┐
│   WEB    │         │  MOBILE  │  │ DESKTOP │  │  ADMIN  │
│   APP    │         │   APP    │  │   APP   │  │  PANEL  │
├──────────┤         ├──────────┤  ├─────────┤  ├─────────┤
│React/Vue │         │ Flutter  │  │Electron │  │  React  │
│          │         │    or    │  │    or   │  │  Admin  │
│Port 3000 │         │React     │  │  Tauri  │  │         │
│          │         │ Native   │  │         │  │Port 3001│
└──────────┘         └──────────┘  └─────────┘  └─────────┘
     │                     │            │             │
     │                     │            │             │
     └─────────────────────┴────────────┴─────────────┘
                           │
           Semua pakai API yang sama: JWT Authentication,
           REST endpoints, real-time updates via WebSockets
```

---

## 1️⃣ Web Application (Primary Interface)

### Technology Stack

```yaml
Framework: React 18+ dengan TypeScript
State Management: Redux Toolkit + RTK Query
UI Library: Material-UI (MUI) v5 atau Ant Design
Routing: React Router v6
API Client: RTK Query (built into Redux Toolkit)
Forms: React Hook Form + Yup validation
Charts: Recharts atau Apache ECharts
Tables: TanStack Table (React Table v8)
File Upload: React Dropzone
QR Scanner: react-qr-scanner
Build Tool: Vite (faster than CRA)
Testing: Vitest + React Testing Library
```

### Project Structure

```
web-app/
├── public/
│   ├── index.html
│   ├── favicon.ico
│   └── assets/
├── src/
│   ├── api/                    # API service definitions
│   │   ├── authApi.ts
│   │   ├── assetApi.ts
│   │   ├── ticketApi.ts
│   │   └── index.ts
│   ├── components/             # Reusable components
│   │   ├── common/
│   │   │   ├── Button.tsx
│   │   │   ├── Input.tsx
│   │   │   ├── Modal.tsx
│   │   │   └── DataTable.tsx
│   │   ├── layout/
│   │   │   ├── Header.tsx
│   │   │   ├── Sidebar.tsx
│   │   │   ├── Footer.tsx
│   │   │   └── DashboardLayout.tsx
│   │   └── features/
│   │       ├── AssetCard.tsx
│   │       ├── TicketList.tsx
│   │       └── UserProfile.tsx
│   ├── features/               # Feature-based modules
│   │   ├── auth/
│   │   │   ├── Login.tsx
│   │   │   ├── authSlice.ts
│   │   │   └── ProtectedRoute.tsx
│   │   ├── assets/
│   │   │   ├── AssetList.tsx
│   │   │   ├── AssetDetail.tsx
│   │   │   ├── AssetForm.tsx
│   │   │   └── assetSlice.ts
│   │   ├── tickets/
│   │   │   ├── TicketList.tsx
│   │   │   ├── TicketDetail.tsx
│   │   │   ├── CreateTicket.tsx
│   │   │   └── ticketSlice.ts
│   │   └── dashboard/
│   │       ├── DirectorDashboard.tsx
│   │       ├── ManagementDashboard.tsx
│   │       └── SLADashboard.tsx
│   ├── hooks/                  # Custom hooks
│   │   ├── useAuth.ts
│   │   ├── usePermissions.ts
│   │   └── useDebounce.ts
│   ├── types/                  # TypeScript types
│   │   ├── Asset.ts
│   │   ├── Ticket.ts
│   │   ├── User.ts
│   │   └── index.ts
│   ├── utils/                  # Utility functions
│   │   ├── formatters.ts
│   │   ├── validators.ts
│   │   └── constants.ts
│   ├── store/                  # Redux store
│   │   ├── index.ts
│   │   └── rootReducer.ts
│   ├── routes/                 # Route definitions
│   │   └── index.tsx
│   ├── App.tsx
│   ├── main.tsx
│   └── vite-env.d.ts
├── .env
├── .env.example
├── package.json
├── tsconfig.json
├── vite.config.ts
└── README.md
```

### Key Features Implementation

#### Authentication Flow

```typescript
// File: src/api/authApi.ts
import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react';

export const authApi = createApi({
  reducerPath: 'authApi',
  baseQuery: fetchBaseQuery({
    baseUrl: import.meta.env.VITE_API_URL + '/api/v1',
    prepareHeaders: (headers, { getState }) => {
      const token = (getState() as RootState).auth.token;
      if (token) {
        headers.set('Authorization', `Bearer ${token}`);
      }
      return headers;
    },
  }),
  endpoints: (builder) => ({
    login: builder.mutation({
      query: (credentials) => ({
        url: '/auth/login',
        method: 'POST',
        body: credentials,
      }),
    }),
    logout: builder.mutation({
      query: () => ({
        url: '/auth/logout',
        method: 'POST',
      }),
    }),
    getCurrentUser: builder.query({
      query: () => '/auth/me',
    }),
  }),
});

export const { useLoginMutation, useLogoutMutation, useGetCurrentUserQuery } = authApi;
```

#### Asset Management Component

```typescript
// File: src/features/assets/AssetList.tsx
import React, { useState } from 'react';
import { useGetAssetsQuery, useDeleteAssetMutation } from '../../api/assetApi';
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import { Button, IconButton, Chip } from '@mui/material';
import { Edit, Delete, QrCode } from '@mui/icons-material';

export const AssetList: React.FC = () => {
  const [page, setPage] = useState(0);
  const [pageSize, setPageSize] = useState(25);
  
  const { data, isLoading, error } = useGetAssetsQuery({
    page: page + 1,
    per_page: pageSize,
  });
  
  const [deleteAsset] = useDeleteAssetMutation();

  const columns: GridColDef[] = [
    { field: 'asset_tag', headerName: 'Asset Tag', width: 150 },
    { field: 'name', headerName: 'Name', width: 200 },
    { field: 'asset_type', headerName: 'Type', width: 150 },
    { 
      field: 'status', 
      headerName: 'Status', 
      width: 120,
      renderCell: (params) => (
        <Chip 
          label={params.value} 
          color={params.value === 'Available' ? 'success' : 'default'}
          size="small"
        />
      )
    },
    { field: 'assigned_to', headerName: 'Assigned To', width: 180 },
    { field: 'location', headerName: 'Location', width: 150 },
    {
      field: 'actions',
      headerName: 'Actions',
      width: 150,
      renderCell: (params) => (
        <>
          <IconButton size="small" onClick={() => handleEdit(params.row.id)}>
            <Edit />
          </IconButton>
          <IconButton size="small" onClick={() => handleViewQR(params.row.id)}>
            <QrCode />
          </IconButton>
          <IconButton size="small" onClick={() => handleDelete(params.row.id)}>
            <Delete />
          </IconButton>
        </>
      ),
    },
  ];

  const handleDelete = async (id: number) => {
    if (window.confirm('Are you sure?')) {
      await deleteAsset(id);
    }
  };

  return (
    <div style={{ height: 600, width: '100%' }}>
      <DataGrid
        rows={data?.data || []}
        columns={columns}
        pageSize={pageSize}
        rowCount={data?.meta.total || 0}
        paginationMode="server"
        onPageChange={setPage}
        onPageSizeChange={setPageSize}
        loading={isLoading}
        disableSelectionOnClick
      />
    </div>
  );
};
```

### Deployment

```dockerfile
# File: Dockerfile

# Build stage
FROM node:18-alpine as build

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# Production stage
FROM nginx:alpine

COPY --from=build /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
```

---

## 2️⃣ Mobile Application (iOS & Android)

### Technology Stack Options

#### Option 1: Flutter (Recommended) ⭐

```yaml
Framework: Flutter 3.16+
Language: Dart
State Management: Riverpod atau Bloc
HTTP Client: Dio
Local Storage: Hive atau Sqflite
Navigation: Go Router
UI: Material Design 3 + Custom widgets
Auth: Flutter Secure Storage untuk token
Push Notifications: Firebase Cloud Messaging
QR Scanner: mobile_scanner
Camera: image_picker
Charts: fl_chart

Keuntungan:
✓ Single codebase untuk iOS + Android
✓ Native performance
✓ Beautiful UI out of the box
✓ Hot reload untuk fast development
✓ Large community & packages

Kekurangan:
✗ Perlu belajar Dart (new language)
✗ App size lebih besar (~20-30MB)
```

#### Option 2: React Native

```yaml
Framework: React Native 0.72+
Language: TypeScript
State Management: Redux Toolkit
HTTP Client: Axios
Local Storage: AsyncStorage
Navigation: React Navigation
UI: React Native Paper atau NativeBase
Auth: @react-native-async-storage
Push Notifications: React Native Firebase
QR Scanner: react-native-camera
Charts: react-native-chart-kit

Keuntungan:
✓ Reuse React knowledge dari web
✓ Code sharing dengan web app
✓ JavaScript ecosystem familiar
✓ Large community

Kekurangan:
✗ Performance slightly lower than Flutter
✗ More native modules needed
✗ Bridge overhead
```

### Flutter Project Structure

```
mobile-app/
├── android/                    # Android native code
├── ios/                        # iOS native code
├── lib/
│   ├── main.dart
│   ├── app.dart
│   ├── config/
│   │   ├── routes.dart
│   │   ├── theme.dart
│   │   └── constants.dart
│   ├── core/
│   │   ├── api/
│   │   │   ├── api_client.dart
│   │   │   └── endpoints.dart
│   │   ├── models/
│   │   │   ├── asset.dart
│   │   │   ├── ticket.dart
│   │   │   └── user.dart
│   │   └── providers/
│   │       ├── auth_provider.dart
│   │       └── asset_provider.dart
│   ├── features/
│   │   ├── auth/
│   │   │   ├── presentation/
│   │   │   │   ├── login_screen.dart
│   │   │   │   └── widgets/
│   │   │   ├── data/
│   │   │   │   └── auth_repository.dart
│   │   │   └── domain/
│   │   │       └── auth_service.dart
│   │   ├── assets/
│   │   │   ├── presentation/
│   │   │   │   ├── asset_list_screen.dart
│   │   │   │   ├── asset_detail_screen.dart
│   │   │   │   ├── scan_qr_screen.dart
│   │   │   │   └── widgets/
│   │   │   ├── data/
│   │   │   │   └── asset_repository.dart
│   │   │   └── domain/
│   │   │       ├── asset.dart
│   │   │       └── asset_service.dart
│   │   ├── tickets/
│   │   │   ├── presentation/
│   │   │   │   ├── ticket_list_screen.dart
│   │   │   │   ├── create_ticket_screen.dart
│   │   │   │   └── widgets/
│   │   │   ├── data/
│   │   │   │   └── ticket_repository.dart
│   │   │   └── domain/
│   │   │       └── ticket_service.dart
│   │   └── dashboard/
│   │       └── presentation/
│   │           └── dashboard_screen.dart
│   ├── shared/
│   │   ├── widgets/
│   │   │   ├── custom_button.dart
│   │   │   ├── custom_textfield.dart
│   │   │   └── loading_indicator.dart
│   │   └── utils/
│   │       ├── formatters.dart
│   │       └── validators.dart
│   └── l10n/                   # Internationalization
│       ├── app_en.arb
│       └── app_id.arb
├── test/
├── pubspec.yaml
└── README.md
```

### Key Features Implementation (Flutter)

#### API Client Setup

```dart
// File: lib/core/api/api_client.dart
import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiClient {
  final Dio _dio;
  final FlutterSecureStorage _storage;
  
  ApiClient({String? baseUrl})
      : _dio = Dio(BaseOptions(
          baseUrl: baseUrl ?? 'http://api.itquty.com/api/v1',
          connectTimeout: const Duration(seconds: 30),
          receiveTimeout: const Duration(seconds: 30),
        )),
        _storage = const FlutterSecureStorage() {
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        // Add JWT token to headers
        final token = await _storage.read(key: 'auth_token');
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
      onError: (DioException e, handler) async {
        if (e.response?.statusCode == 401) {
          // Token expired, redirect to login
          await _storage.delete(key: 'auth_token');
          // Navigate to login screen
        }
        return handler.next(e);
      },
    ));
  }
  
  Future<Response> get(String path, {Map<String, dynamic>? queryParameters}) {
    return _dio.get(path, queryParameters: queryParameters);
  }
  
  Future<Response> post(String path, {dynamic data}) {
    return _dio.post(path, data: data);
  }
  
  Future<Response> put(String path, {dynamic data}) {
    return _dio.put(path, data: data);
  }
  
  Future<Response> delete(String path) {
    return _dio.delete(path);
  }
}
```

#### Asset List Screen

```dart
// File: lib/features/assets/presentation/asset_list_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class AssetListScreen extends ConsumerWidget {
  const AssetListScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final assetsAsync = ref.watch(assetsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Assets'),
        actions: [
          IconButton(
            icon: const Icon(Icons.qr_code_scanner),
            onPressed: () => _navigateToQRScanner(context),
          ),
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: () => _showFilterDialog(context),
          ),
        ],
      ),
      body: assetsAsync.when(
        data: (assets) => ListView.builder(
          itemCount: assets.length,
          itemBuilder: (context, index) {
            final asset = assets[index];
            return AssetCard(
              asset: asset,
              onTap: () => _navigateToDetail(context, asset.id),
            );
          },
        ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => Center(
          child: Text('Error: $error'),
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _navigateToCreateAsset(context),
        child: const Icon(Icons.add),
      ),
    );
  }
}

class AssetCard extends StatelessWidget {
  final Asset asset;
  final VoidCallback onTap;

  const AssetCard({
    Key? key,
    required this.asset,
    required this.onTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ListTile(
        leading: CircleAvatar(
          child: Text(asset.assetTag.substring(0, 2)),
        ),
        title: Text(asset.name),
        subtitle: Text('${asset.assetType} • ${asset.location}'),
        trailing: Chip(
          label: Text(asset.status),
          backgroundColor: _getStatusColor(asset.status),
        ),
        onTap: onTap,
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'available':
        return Colors.green.shade100;
      case 'assigned':
        return Colors.blue.shade100;
      case 'maintenance':
        return Colors.orange.shade100;
      default:
        return Colors.grey.shade100;
    }
  }
}
```

#### QR Code Scanner

```dart
// File: lib/features/assets/presentation/scan_qr_screen.dart
import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

class ScanQRScreen extends StatefulWidget {
  const ScanQRScreen({Key? key}) : super(key: key);

  @override
  State<ScanQRScreen> createState() => _ScanQRScreenState();
}

class _ScanQRScreenState extends State<ScanQRScreen> {
  final MobileScannerController controller = MobileScannerController();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Scan Asset QR Code'),
        actions: [
          IconButton(
            icon: Icon(controller.torchEnabled ? Icons.flash_on : Icons.flash_off),
            onPressed: () => controller.toggleTorch(),
          ),
        ],
      ),
      body: MobileScanner(
        controller: controller,
        onDetect: (capture) {
          final List<Barcode> barcodes = capture.barcodes;
          for (final barcode in barcodes) {
            if (barcode.rawValue != null) {
              _handleScannedCode(barcode.rawValue!);
              break;
            }
          }
        },
      ),
    );
  }

  void _handleScannedCode(String code) {
    // Navigate to asset detail dengan scanned code
    Navigator.pop(context, code);
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }
}
```

### Mobile App Build & Deployment

```bash
# Flutter Build Commands

# Development build
flutter run

# Release build for Android
flutter build apk --release
# Output: build/app/outputs/flutter-apk/app-release.apk

# Release build for iOS (requires Mac)
flutter build ios --release
# Then open Xcode and archive

# Build App Bundle for Play Store
flutter build appbundle --release
# Output: build/app/outputs/bundle/release/app-release.aab

# Install on device
flutter install

# Run tests
flutter test
```

---

## 3️⃣ Desktop Application (Windows, macOS, Linux)

### Technology Stack Options

#### Option 1: Electron (Recommended untuk reuse web code)

```yaml
Framework: Electron 28+
Web Framework: React/Vue (reuse dari web app)
Language: TypeScript
Build Tool: Electron Builder
Auto Update: electron-updater
System Tray: Electron API
Native Features: Electron IPC

Keuntungan:
✓ Reuse entire web app code
✓ Cross-platform (Win/Mac/Linux)
✓ Access to native APIs
✓ Familiar web technologies
✓ Large community

Kekurangan:
✗ Large app size (~100-200MB)
✗ High memory usage
✗ Chromium overhead
```

#### Option 2: Tauri (Lightweight alternative)

```yaml
Framework: Tauri 1.5+
Web Framework: React/Vue
Backend: Rust
Build Size: ~5-15MB (much smaller!)
System Tray: Tauri API
Native Features: Rust commands

Keuntungan:
✓ Tiny app size
✓ Low memory usage
✓ Better performance
✓ Secure by default

Kekurangan:
✗ Smaller community
✗ Need to learn some Rust
✗ Fewer plugins
```

### Electron Project Structure

```
desktop-app/
├── electron/
│   ├── main.ts                 # Main process
│   ├── preload.ts              # Preload script
│   └── ipc/                    # IPC handlers
│       ├── asset-handler.ts
│       └── print-handler.ts
├── src/                        # Web app code (reuse from web-app)
│   ├── components/
│   ├── features/
│   └── ...
├── public/
├── build/                      # Built electron app
├── package.json
├── electron-builder.json       # Build configuration
└── README.md
```

### Main Process Configuration

```typescript
// File: electron/main.ts
import { app, BrowserWindow, ipcMain, Tray, Menu } from 'electron';
import path from 'path';
import { autoUpdater } from 'electron-updater';

let mainWindow: BrowserWindow | null = null;
let tray: Tray | null = null;

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1280,
    height: 800,
    minWidth: 1024,
    minHeight: 768,
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      preload: path.join(__dirname, 'preload.js'),
    },
    icon: path.join(__dirname, '../public/icon.png'),
  });

  // Load app
  if (process.env.NODE_ENV === 'development') {
    mainWindow.loadURL('http://localhost:3000');
    mainWindow.webContents.openDevTools();
  } else {
    mainWindow.loadFile(path.join(__dirname, '../dist/index.html'));
  }

  // System tray
  createTray();

  // Auto updater
  autoUpdater.checkForUpdatesAndNotify();
}

function createTray() {
  tray = new Tray(path.join(__dirname, '../public/tray-icon.png'));
  
  const contextMenu = Menu.buildFromTemplate([
    { label: 'Show App', click: () => mainWindow?.show() },
    { label: 'Quit', click: () => app.quit() },
  ]);
  
  tray.setToolTip('ITQuty Asset Management');
  tray.setContextMenu(contextMenu);
  
  tray.on('click', () => {
    mainWindow?.show();
  });
}

// IPC Handlers untuk native features
ipcMain.handle('print-asset-label', async (event, assetData) => {
  // Print asset label using node-thermal-printer
  const { printLabel } = await import('./utils/printer');
  return printLabel(assetData);
});

ipcMain.handle('export-to-excel', async (event, data) => {
  // Export data to Excel using exceljs
  const { exportToExcel } = await import('./utils/excel');
  return exportToExcel(data);
});

ipcMain.handle('get-system-info', async () => {
  const os = require('os');
  return {
    platform: os.platform(),
    hostname: os.hostname(),
    memory: os.totalmem(),
    cpus: os.cpus().length,
  };
});

app.whenReady().then(createWindow);

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('activate', () => {
  if (BrowserWindow.getAllWindows().length === 0) {
    createWindow();
  }
});
```

### Build Configuration

```json
// File: electron-builder.json
{
  "appId": "com.itquty.assetmanagement",
  "productName": "ITQuty Asset Management",
  "directories": {
    "output": "release",
    "buildResources": "build"
  },
  "files": [
    "dist/**/*",
    "electron/**/*"
  ],
  "win": {
    "target": ["nsis", "portable"],
    "icon": "build/icon.ico"
  },
  "nsis": {
    "oneClick": false,
    "allowToChangeInstallationDirectory": true,
    "createDesktopShortcut": true,
    "createStartMenuShortcut": true
  },
  "mac": {
    "target": ["dmg", "zip"],
    "category": "public.app-category.business",
    "icon": "build/icon.icns"
  },
  "linux": {
    "target": ["AppImage", "deb"],
    "category": "Utility",
    "icon": "build/icon.png"
  },
  "publish": {
    "provider": "github",
    "owner": "santz1994",
    "repo": "itquty-desktop"
  }
}
```

### Build Commands

```bash
# Development
npm run electron:dev

# Build for Windows
npm run electron:build:win
# Output: release/ITQuty Setup 1.0.0.exe

# Build for Mac
npm run electron:build:mac
# Output: release/ITQuty-1.0.0.dmg

# Build for Linux
npm run electron:build:linux
# Output: release/ITQuty-1.0.0.AppImage

# Build for all platforms
npm run electron:build:all
```

---

## 4️⃣ Admin Panel (Separate Admin Interface)

### Purpose
```yaml
Target Users: Super Admin, IT Admin
Features:
  - System configuration
  - User management
  - Role & permission management
  - System monitoring
  - Audit logs
  - Database management
  - Backup & restore

Tech Stack:
  - React Admin (powerful admin framework)
  - Material-UI
  - Chart.js untuk visualizations
  - Socket.io untuk real-time monitoring
```

### React Admin Setup

```typescript
// File: admin-panel/src/App.tsx
import { Admin, Resource, ListGuesser } from 'react-admin';
import jsonServerProvider from 'ra-data-json-server';
import { UserList, UserEdit, UserCreate } from './resources/users';
import { AssetList, AssetEdit } from './resources/assets';
import { Dashboard } from './Dashboard';

const dataProvider = jsonServerProvider('http://localhost:8000/api/v1');

const App = () => (
  <Admin dataProvider={dataProvider} dashboard={Dashboard}>
    <Resource name="users" list={UserList} edit={UserEdit} create={UserCreate} />
    <Resource name="assets" list={AssetList} edit={AssetEdit} />
    <Resource name="tickets" list={ListGuesser} />
    <Resource name="audit-logs" list={ListGuesser} />
  </Admin>
);

export default App;
```

---

## 🔄 Code Sharing Strategy

### Shared Code Between Platforms

```
shared/
├── types/                  # TypeScript interfaces (Web, Desktop)
│   ├── Asset.ts
│   ├── Ticket.ts
│   └── User.ts
├── constants/              # Constants (All platforms)
│   ├── apiEndpoints.ts
│   ├── statusColors.ts
│   └── validationRules.ts
├── utils/                  # Utility functions (All platforms)
│   ├── formatters.ts       # Date, currency formatters
│   ├── validators.ts       # Input validation
│   └── calculations.ts     # Business logic calculations
└── api/                    # API client interface (All platforms)
    └── baseClient.ts       # Abstract API client
```

### Platform-Specific Implementation

```typescript
// Shared interface
// File: shared/api/baseClient.ts
export interface IApiClient {
  get<T>(url: string, params?: any): Promise<T>;
  post<T>(url: string, data: any): Promise<T>;
  put<T>(url: string, data: any): Promise<T>;
  delete<T>(url: string): Promise<T>;
}

// Web implementation
// File: web-app/src/api/webClient.ts
import axios from 'axios';
import { IApiClient } from '@shared/api/baseClient';

export class WebApiClient implements IApiClient {
  async get<T>(url: string, params?: any): Promise<T> {
    const response = await axios.get(url, { params });
    return response.data;
  }
  // ... other methods
}

// Mobile implementation (Flutter)
// File: mobile-app/lib/core/api/mobile_client.dart
class MobileApiClient implements ApiClient {
  Future<T> get<T>(String url, {Map<String, dynamic>? params}) async {
    final response = await dio.get(url, queryParameters: params);
    return response.data as T;
  }
  // ... other methods
}
```

---

## 📊 Feature Comparison Matrix

| Feature | Web App | Mobile App | Desktop App | Admin Panel |
|---------|---------|------------|-------------|-------------|
| **Asset Management** | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **Ticket Management** | ✅ Full | ✅ Full | ✅ Full | ✅ Full |
| **QR Scanning** | 📷 With camera | ✅ Native | 📷 With camera | ❌ |
| **Offline Mode** | ❌ | ✅ Yes | ✅ Yes | ❌ |
| **Push Notifications** | 🔔 Web Push | ✅ Native | 🔔 System | ❌ |
| **Print Labels** | 🖨️ Browser print | ❌ | ✅ Native print | 🖨️ Browser |
| **Bulk Operations** | ✅ Yes | ⚠️ Limited | ✅ Yes | ✅ Yes |
| **System Config** | ❌ | ❌ | ❌ | ✅ Full |
| **Monitoring** | ❌ | ❌ | ❌ | ✅ Full |
| **File Size** | N/A | ~30MB | ~150MB | N/A |
| **Auto Updates** | ✅ Instant | ✅ App Store | ✅ Built-in | ✅ Instant |

---

## 🎯 Development Priority

### Phase 1 (Month 1-3)
```
✅ Backend API complete
✅ Web Application (80% feature parity dengan current app)
   - Authentication
   - Asset CRUD
   - Ticket CRUD
   - Basic dashboards
```

### Phase 2 (Month 4-6)
```
✅ Web Application (100% feature parity)
   - All features from monolith
   - Advanced reports
   - Bulk operations
✅ Mobile App (60% - core features)
   - View assets
   - Create tickets
   - QR scanning
   - Basic offline
```

### Phase 3 (Month 7-9)
```
✅ Mobile App (100%)
   - Full offline sync
   - Push notifications
   - Advanced features
✅ Desktop App (Beta)
   - Basic functionality
   - Native printing
```

### Phase 4 (Month 10-12)
```
✅ Desktop App (Production)
   - Auto updates
   - System tray
   - Advanced printing
✅ Admin Panel
   - System configuration
   - Monitoring
```

---

## 💡 Best Practices

### 1. API-First Design
```typescript
// Always define API contract first
// Then implement on all platforms

interface AssetApi {
  getAssets(params: GetAssetsParams): Promise<AssetListResponse>;
  getAsset(id: number): Promise<Asset>;
  createAsset(data: CreateAssetData): Promise<Asset>;
  updateAsset(id: number, data: UpdateAssetData): Promise<Asset>;
  deleteAsset(id: number): Promise<void>;
}
```

### 2. Consistent UX Across Platforms
```yaml
Design System:
  - Same color palette
  - Same terminology
  - Same workflows
  - Platform-specific patterns (Material vs Cupertino)
```

### 3. Progressive Enhancement
```yaml
Core Features: Work on all platforms
Enhanced Features: Platform-specific
  - Mobile: Native camera, offline sync
  - Desktop: Native printing, system integration
  - Web: Browser features, responsive design
```

### 4. Performance Optimization
```typescript
// Implement pagination, lazy loading, caching
const assets = useInfiniteQuery(
  ['assets'],
  ({ pageParam = 1 }) => fetchAssets(pageParam),
  {
    getNextPageParam: (lastPage) => lastPage.nextPage,
    staleTime: 5 * 60 * 1000, // 5 minutes cache
  }
);
```

---

## 📚 Documentation & Resources

### For Developers
```
1. API Documentation: Swagger/OpenAPI at http://localhost:8000/docs
2. Component Storybook: http://localhost:6006
3. Style Guide: Figma design system
4. Getting Started: README.md per platform
```

### Learning Resources
```
Web (React):
  - https://react.dev
  - https://redux-toolkit.js.org
  - https://mui.com

Mobile (Flutter):
  - https://flutter.dev
  - https://riverpod.dev
  - https://pub.dev

Desktop (Electron):
  - https://electronjs.org
  - https://electron-builder.org
```

---

## ✅ Summary

**Multi-Platform Strategy:**
1. ✅ **Single Backend API** - All platforms consume same API
2. ✅ **Web First** - Primary interface, easiest to develop
3. ✅ **Mobile Native** - Flutter untuk best user experience
4. ✅ **Desktop Electron** - Reuse web code, add native features
5. ✅ **Admin Panel** - Specialized admin interface

**Timeline:**
- Web App: 3 months (complete)
- Mobile App: 6 months (core features + polish)
- Desktop App: 9 months (beta → production)
- Admin Panel: 12 months (final touch)

**Team Requirements:**
- 2 Frontend Developers (Web + Desktop)
- 1 Mobile Developer (Flutter)
- 1 UI/UX Designer
- 1 QA Engineer

---

**Next Document:** [07_DEVOPS_INFRASTRUCTURE.md](./07_DEVOPS_INFRASTRUCTURE.md)  
**Related:** [02_ARSITEKTUR_DETAIL_MICROSERVICES.md](./02_ARSITEKTUR_DETAIL_MICROSERVICES.md)
