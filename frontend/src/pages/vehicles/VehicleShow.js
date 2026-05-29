import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import { vehicleApi } from '../../services/api';

function VehicleShow() {
    const { id } = useParams();
    const [vehicle, setVehicle] = useState(null);
    const [bookings, setBookings] = useState([]);
    const [maintenanceLogs, setMaintenanceLogs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [maintForm, setMaintForm] = useState({ maintenance_type: '', description: '', cost: '', maintenance_date: new Date().toISOString().split('T')[0], mileage_at_service: '', service_provider: '' });

    useEffect(() => {
        loadData();
    }, [id]);

    const loadData = async () => {
        try {
            const [vRes, bRes, mRes] = await Promise.all([
                vehicleApi.getById(id),
                vehicleApi.getBookings({ vehicle_id: id }),
                vehicleApi.getMaintenanceLogs(id)
            ]);
            setVehicle(vRes.data.data);
            setBookings(bRes.data.data);
            setMaintenanceLogs(mRes.data.data);
            setMaintForm(prev => ({ ...prev, mileage_at_service: vRes.data.data.current_mileage }));
        } catch (err) {
            toast.error('Gagal memuat data kendaraan.');
        }
        setLoading(false);
    };

    const handleMaintSubmit = async (e) => {
        e.preventDefault();
        try {
            await vehicleApi.addMaintenance(id, maintForm);
            toast.success('Log maintenance berhasil ditambahkan.');
            loadData();
            setMaintForm({ maintenance_type: '', description: '', cost: '', maintenance_date: new Date().toISOString().split('T')[0], mileage_at_service: vehicle?.current_mileage || '', service_provider: '' });
        } catch (err) {
            toast.error('Gagal menambah log maintenance.');
        }
    };

    if (loading) return <div className="text-center p-5"><i className="fa fa-spinner fa-spin fa-3x"></i></div>;
    if (!vehicle) return <div className="alert alert-danger">Kendaraan tidak ditemukan.</div>;

    const statusColor = { available: 'green', in_use: 'blue', maintenance: 'yellow', retired: 'gray' };

    return (
        <div className="container-fluid">
            <div className="row">
                <div className="col-md-4">
                    <div className="box box-widget widget-user-2">
                        <div className={`widget-user-header bg-${statusColor[vehicle.status] || 'gray'}`}>
                            <h3 className="widget-user-username">{vehicle.name}</h3>
                            <h5 className="widget-user-desc">{vehicle.brand} {vehicle.model}</h5>
                        </div>
                        <div className="box-footer no-padding">
                            <ul className="nav nav-stacked">
                                <li><a href="#">Plat Nomor <span className="pull-right badge bg-blue">{vehicle.plate_number}</span></a></li>
                                <li><a href="#">Tahun <span className="pull-right badge bg-aqua">{vehicle.year || '-'}</span></a></li>
                                <li><a href="#">Warna <span className="pull-right badge bg-gray">{vehicle.color || '-'}</span></a></li>
                                <li><a href="#">Kapasitas <span className="pull-right badge bg-purple">{vehicle.capacity} orang</span></a></li>
                                <li><a href="#">Bahan Bakar <span className="pull-right badge bg-maroon">{vehicle.fuel_type || '-'}</span></a></li>
                                <li><a href="#">Kilometer <span className="pull-right badge bg-teal">{Number(vehicle.current_mileage).toLocaleString()} km</span></a></li>
                            </ul>
                        </div>
                    </div>

                    <div className="box box-warning">
                        <div className="box-header with-border"><h3 className="box-title"><i className="fa fa-wrench"></i> Maintenance</h3></div>
                        <div className="box-body">
                            {maintenanceLogs.length > 0 ? maintenanceLogs.map(log => (
                                <div key={log.id} className="post">
                                    <p><strong>{log.maintenance_type}</strong> - {log.maintenance_date}</p>
                                    <p className="text-muted">{log.description}</p>
                                    <p><small>Biaya: Rp {Number(log.cost || 0).toLocaleString()} | Vendor: {log.service_provider || '-'}</small></p>
                                    <hr />
                                </div>
                            )) : <p className="text-muted text-center">Belum ada log maintenance.</p>}
                        </div>
                    </div>
                </div>

                <div className="col-md-8">
                    <div className="box box-primary">
                        <div className="box-header with-border">
                            <h3 className="box-title"><i className="fa fa-history"></i> Riwayat Booking</h3>
                            <div className="box-tools pull-right">
                                <Link to="/vehicles" className="btn btn-default btn-sm"><i className="fa fa-arrow-left"></i> Kembali</Link>
                                <Link to={`/vehicles/${id}/edit`} className="btn btn-warning btn-sm" style={{ marginLeft: 5 }}><i className="fa fa-edit"></i> Edit</Link>
                                <Link to={`/vehicle-bookings/create?vehicle_id=${id}`} className="btn btn-success btn-sm" style={{ marginLeft: 5 }}><i className="fa fa-car"></i> Booking</Link>
                            </div>
                        </div>
                        <div className="box-body table-responsive no-padding">
                            <table className="table table-hover">
                                <thead><tr><th>Tanggal</th><th>Pemohon</th><th>Tujuan</th><th>Status</th><th>Aksi</th></tr></thead>
                                <tbody>
                                    {bookings.length > 0 ? bookings.map(b => (
                                        <tr key={b.id}>
                                            <td>{b.start_datetime}<br /><small className="text-muted">s/d {b.end_datetime}</small></td>
                                            <td>{b.requester?.name || '-'}</td>
                                            <td>{b.destination}</td>
                                            <td><span className={`label label-${b.status === 'approved' ? 'success' : b.status === 'pending' ? 'warning' : b.status === 'rejected' ? 'danger' : 'info'}`}>{b.status}</span></td>
                                            <td><Link to={`/vehicle-bookings/${b.id}`} className="btn btn-xs btn-default"><i className="fa fa-eye"></i></Link></td>
                                        </tr>
                                    )) : <tr><td colSpan="5" className="text-center text-muted">Belum ada booking.</td></tr>}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="box box-warning">
                        <div className="box-header with-border"><h3 className="box-title"><i className="fa fa-plus"></i> Tambah Log Maintenance</h3></div>
                        <form onSubmit={handleMaintSubmit}>
                            <div className="box-body">
                                <div className="row">
                                    <div className="col-md-6">
                                        <div className="form-group"><label>Tipe *</label><select name="maintenance_type" className="form-control" value={maintForm.maintenance_type} onChange={e => setMaintForm({ ...maintForm, maintenance_type: e.target.value })} required><option value="">Pilih</option><option>Servis Berkala</option><option>Perbaikan</option><option>Penggantian Sparepart</option><option>Pencucian</option></select></div>
                                        <div className="form-group"><label>Tanggal *</label><input type="date" className="form-control" value={maintForm.maintenance_date} onChange={e => setMaintForm({ ...maintForm, maintenance_date: e.target.value })} required /></div>
                                        <div className="form-group"><label>Biaya (Rp)</label><input type="number" className="form-control" value={maintForm.cost} onChange={e => setMaintForm({ ...maintForm, cost: e.target.value })} min="0" /></div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="form-group"><label>Deskripsi *</label><textarea className="form-control" rows="3" value={maintForm.description} onChange={e => setMaintForm({ ...maintForm, description: e.target.value })} required /></div>
                                        <div className="form-group"><label>KM Saat Ini</label><input type="number" className="form-control" value={maintForm.mileage_at_service} onChange={e => setMaintForm({ ...maintForm, mileage_at_service: e.target.value })} /></div>
                                        <div className="form-group"><label>Vendor</label><input type="text" className="form-control" value={maintForm.service_provider} onChange={e => setMaintForm({ ...maintForm, service_provider: e.target.value })} /></div>
                                    </div>
                                </div>
                            </div>
                            <div className="box-footer"><button type="submit" className="btn btn-warning"><i className="fa fa-save"></i> Simpan Log</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default VehicleShow;