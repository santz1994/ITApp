import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { toast } from 'react-toastify';
import { vehicleApi } from '../../services/api';

function VehicleMyBookings() {
    const [bookings, setBookings] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        vehicleApi.getMyBookings().then(res => { setBookings(res.data.data); setLoading(false); }).catch(() => { toast.error('Gagal memuat data.'); setLoading(false); });
    }, []);

    const statusBadge = { pending: 'warning', approved: 'success', rejected: 'danger', in_progress: 'info', completed: 'primary', cancelled: 'secondary' };

    return (
        <div className="container-fluid">
            <div className="box box-primary">
                <div className="box-header with-border">
                    <h3 className="box-title"><i className="fa fa-calendar"></i> Booking Saya</h3>
                    <div className="box-tools pull-right"><Link to="/vehicle-bookings/create" className="btn btn-success btn-sm"><i className="fa fa-plus"></i> Booking Baru</Link></div>
                </div>
                <div className="box-body table-responsive no-padding">
                    {loading ? <div className="text-center p-5"><i className="fa fa-spinner fa-spin fa-3x"></i></div> : (
                        <table className="table table-hover">
                            <thead><tr><th>ID</th><th>Kendaraan</th><th>Tujuan</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
                            <tbody>
                                {bookings.map(b => (
                                    <tr key={b.id}>
                                        <td>#{b.id}</td>
                                        <td>{b.vehicle?.name || '-'}<br /><small className="text-muted">{b.vehicle?.plate_number || ''}</small></td>
                                        <td>{b.destination}</td>
                                        <td>{b.start_datetime}<br /><small className="text-muted">s/d {b.end_datetime}</small></td>
                                        <td><span className={`label label-${statusBadge[b.status]}`}>{b.status.replace('_', ' ')}</span></td>
                                        <td><Link to={`/vehicle-bookings/${b.id}`} className="btn btn-xs btn-default"><i className="fa fa-eye"></i></Link></td>
                                    </tr>
                                ))}
                                {bookings.length === 0 && <tr><td colSpan="6" className="text-center text-muted" style={{ padding: 30 }}>Belum ada booking kendaraan.</td></tr>}
                            </tbody>
                        </table>
                    )}
                </div>
            </div>
        </div>
    );
}

export default VehicleMyBookings;