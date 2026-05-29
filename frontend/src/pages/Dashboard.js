import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Link } from 'react-router-dom';
import { fetchPendingApprovals } from '../store/slices/approvalSlice';
import { fetchItems } from '../store/slices/inventorySlice';
import { fetchVehicles } from '../store/slices/vehicleSlice';

function Dashboard() {
    const dispatch = useDispatch();
    const { user } = useSelector((state) => state.auth);
    const { vehicles } = useSelector((state) => state.vehicles);
    const { items, stats } = useSelector((state) => state.inventory);
    const { pendingApprovals } = useSelector((state) => state.approvals);

    useEffect(() => {
        dispatch(fetchVehicles());
        dispatch(fetchItems());
        dispatch(fetchPendingApprovals());
    }, [dispatch]);

    return (
        <div className="container-fluid">
            <h2>Selamat datang, {user?.name || 'User'}!</h2>
            <p className="text-muted">Integrated Management System PT Quty Karunia</p>

            <div className="row" style={{ marginTop: 20 }}>
                <div className="col-md-3 col-sm-6">
                    <div className="info-box bg-aqua">
                        <span className="info-box-icon"><i className="fa fa-car"></i></span>
                        <div className="info-box-content">
                            <span className="info-box-text">Kendaraan</span>
                            <span className="info-box-number">{vehicles.length}</span>
                            <Link to="/vehicles" style={{ color: 'white' }}>Lihat Detail <i className="fa fa-arrow-circle-right"></i></Link>
                        </div>
                    </div>
                </div>
                <div className="col-md-3 col-sm-6">
                    <div className="info-box bg-green">
                        <span className="info-box-icon"><i className="fa fa-cubes"></i></span>
                        <div className="info-box-content">
                            <span className="info-box-text">Item Inventaris</span>
                            <span className="info-box-number">{stats.total_items || items.length}</span>
                            <Link to="/inventory" style={{ color: 'white' }}>Lihat Detail <i className="fa fa-arrow-circle-right"></i></Link>
                        </div>
                    </div>
                </div>
                <div className="col-md-3 col-sm-6">
                    <div className="info-box bg-yellow">
                        <span className="info-box-icon"><i className="fa fa-exclamation-triangle"></i></span>
                        <div className="info-box-content">
                            <span className="info-box-text">Stok Rendah</span>
                            <span className="info-box-number">{stats.low_stock_items || 0}</span>
                            <Link to="/inventory" style={{ color: 'white' }}>Lihat Detail <i className="fa fa-arrow-circle-right"></i></Link>
                        </div>
                    </div>
                </div>
                <div className="col-md-3 col-sm-6">
                    <div className="info-box bg-red">
                        <span className="info-box-icon"><i className="fa fa-check-double"></i></span>
                        <div className="info-box-content">
                            <span className="info-box-text">Pending Approval</span>
                            <span className="info-box-number">{pendingApprovals.length}</span>
                            <Link to="/approvals" style={{ color: 'white' }}>Lihat Detail <i className="fa fa-arrow-circle-right"></i></Link>
                        </div>
                    </div>
                </div>
            </div>

            <div className="row" style={{ marginTop: 20 }}>
                <div className="col-md-6">
                    <div className="box box-primary">
                        <div className="box-header with-border">
                            <h3 className="box-title"><i className="fa fa-link"></i> Akses Cepat</h3>
                        </div>
                        <div className="box-body">
                            <div className="row">
                                <div className="col-sm-6">
                                    <Link to="/vehicle-bookings/create" className="btn btn-app" style={{ width: '100%', marginBottom: 10 }}>
                                        <i className="fa fa-car"></i> Booking Kendaraan
                                    </Link>
                                </div>
                                <div className="col-sm-6">
                                    <Link to="/inventory-requests/create" className="btn btn-app" style={{ width: '100%', marginBottom: 10 }}>
                                        <i className="fa fa-file-alt"></i> Request ATK/Sparepart
                                    </Link>
                                </div>
                            </div>
                            <div className="row">
                                <div className="col-sm-6">
                                    <Link to="/vehicle-bookings/my" className="btn btn-app" style={{ width: '100%', marginBottom: 10 }}>
                                        <i className="fa fa-calendar"></i> Booking Saya
                                    </Link>
                                </div>
                                <div className="col-sm-6">
                                    <Link to="/approvals" className="btn btn-app" style={{ width: '100%', marginBottom: 10 }}>
                                        <i className="fa fa-check"></i> Pending Approvals
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="col-md-6">
                    <div className="box box-info">
                        <div className="box-header with-border">
                            <h3 className="box-title"><i className="fa fa-info-circle"></i> Info Sistem</h3>
                        </div>
                        <div className="box-body">
                            <p><strong>Sistem:</strong> Integrated Management System</p>
                            <p><strong>Modul Aktif:</strong></p>
                            <ul>
                                <li><i className="fa fa-check text-green"></i> Meeting Room Management</li>
                                <li><i className="fa fa-check text-green"></i> Vehicle Management</li>
                                <li><i className="fa fa-check text-green"></i> ATK & Sparepart Inventory</li>
                                <li><i className="fa fa-check text-green"></i> Multi-tier Approval Workflow</li>
                                <li><i className="fa fa-check text-green"></i> Notification System</li>
                                <li><i className="fa fa-check text-green"></i> Reporting & Analytics</li>
                                <li><i className="fa fa-check text-green"></i> User & Role Management (RBAC)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default Dashboard;