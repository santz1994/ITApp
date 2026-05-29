import { useEffect, useState } from 'react';
import { useSelector } from 'react-redux';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import { vehicleApi } from '../../services/api';

function VehicleBookingShow() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { user } = useSelector((state) => state.auth);
    const [booking, setBooking] = useState(null);
    const [loading, setLoading] = useState(true);
    const [rejectReason, setRejectReason] = useState('');
    const [actualData, setActualData] = useState({ actual_distance: '', actual_fuel_cost: '', notes: '' });

    useEffect(() => {
        vehicleApi.getBooking(id).then(res => { setBooking(res.data.data); setLoading(false); }).catch(() => { toast.error('Gagal memuat data booking.'); setLoading(false); });
    }, [id]);

    const handleAction = async (action) => {
        try {
            if (action === 'approve') { await vehicleApi.approveBooking(id); toast.success('Booking disetujui.'); }
            else if (action === 'reject') { await vehicleApi.rejectBooking(id, { rejection_reason: rejectReason }); toast.success('Booking ditolak.'); }
            else if (action === 'cancel') { await vehicleApi.cancelBooking(id); toast.success('Booking dibatalkan.'); }
            else if (action === 'start') { await vehicleApi.startTrip(id); toast.success('Perjalanan dimulai.'); }
            const res = await vehicleApi.getBooking(id);
            setBooking(res.data.data);
        } catch (err) { toast.error(err.response?.data?.message || 'Gagal memproses aksi.'); }
    };

    const handleComplete = async (e) => {
        e.preventDefault();
        try {
            await vehicleApi.completeTrip(id, actualData);
            toast.success('Perjalanan selesai.');
            const res = await vehicleApi.getBooking(id);
            setBooking(res.data.data);
        } catch (err) { toast.error('Gagal menyelesaikan perjalanan.'); }
    };

    if (loading) return <div className="text-center p-5"><i className="fa fa-spinner fa-spin fa-3x"></i></div>;
    if (!booking) return <div className="alert alert-danger">Booking tidak ditemukan.</div>;

    const statusBadge = { pending: 'warning', approved: 'success', rejected: 'danger', in_progress: 'info', completed: 'primary', cancelled: 'secondary' };

    return (
        <div className="container-fluid">
            <div className="row">
                <div className="col-md-8">
                    <div className="box box-primary">
                        <div className="box-header with-border">
                            <h3 className="box-title"><i className="fa fa-info-circle"></i> Detail Booking #{booking.id}</h3>
                            <div className="box-tools pull-right"><span className={`label label-${statusBadge[booking.status]}`}>{booking.status.replace('_', ' ').toUpperCase()}</span></div>
                        </div>
                        <div className="box-body">
                            <div className="row">
                                <div className="col-md-6">
                                    <table className="table table-bordered">
                                        <tr><th width="40%">Kendaraan</th><td>{booking.vehicle?.name || '-'}</td></tr>
                                        <tr><th>Tujuan</th><td>{booking.destination}</td></tr>
                                        <tr><th>Keperluan</th><td>{booking.purpose}</td></tr>
                                        <tr><th>Penumpang</th><td>{booking.passengers} orang</td></tr>
                                    </table>
                                </div>
                                <div className="col-md-6">
                                    <table className="table table-bordered">
                                        <tr><th width="40%">Waktu Mulai</th><td>{booking.start_datetime}</td></tr>
                                        <tr><th>Waktu Selesai</th><td>{booking.end_datetime}</td></tr>
                                        <tr><th>Estimasi Jarak</th><td>{booking.estimated_distance ? `${booking.estimated_distance} km` : '-'}</td></tr>
                                        <tr><th>Catatan</th><td>{booking.notes || '-'}</td></tr>
                                    </table>
                                </div>
                            </div>
                            {booking.rejection_reason && <div className="alert alert-danger"><h4><i className="fa fa-ban"></i> Alasan Penolakan</h4><p>{booking.rejection_reason}</p></div>}
                        </div>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="box box-solid">
                        <div className="box-header with-border"><h3 className="box-title"><i className="fa fa-cogs"></i> Aksi</h3></div>
                        <div className="box-body">
                            {booking.status === 'pending' && <>
                                <button onClick={() => handleAction('approve')} className="btn btn-success btn-block"><i className="fa fa-check"></i> Setujui</button><hr />
                                <textarea className="form-control" rows="2" placeholder="Alasan penolakan..." value={rejectReason} onChange={e => setRejectReason(e.target.value)} />
                                <button onClick={() => handleAction('reject')} className="btn btn-danger btn-block" style={{ marginTop: 5 }}><i className="fa fa-times"></i> Tolak</button>
                            </>}
                            {booking.status === 'approved' && <button onClick={() => handleAction('start')} className="btn btn-info btn-block"><i className="fa fa-car"></i> Mulai Perjalanan</button>}
                            {booking.status === 'in_progress' && <form onSubmit={handleComplete}><div className="form-group"><label>Jarak Aktual (km)</label><input type="number" className="form-control" value={actualData.actual_distance} onChange={e => setActualData({ ...actualData, actual_distance: e.target.value })} /></div><div className="form-group"><label>Biaya BBM (Rp)</label><input type="number" className="form-control" value={actualData.actual_fuel_cost} onChange={e => setActualData({ ...actualData, actual_fuel_cost: e.target.value })} /></div><button type="submit" className="btn btn-success btn-block"><i className="fa fa-flag-checkered"></i> Selesaikan</button></form>}
                            {['pending', 'approved'].includes(booking.status) && <><hr /><button onClick={() => { if (window.confirm('Yakin batalkan?')) handleAction('cancel'); }} className="btn btn-default btn-block"><i className="fa fa-ban"></i> Batalkan</button></>}
                            <hr /><button onClick={() => navigate('/vehicle-bookings/my')} className="btn btn-default btn-block"><i className="fa fa-arrow-left"></i> Kembali</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default VehicleBookingShow;