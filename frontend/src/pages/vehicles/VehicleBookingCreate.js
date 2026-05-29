import { useEffect, useState } from 'react';
import { useDispatch } from 'react-redux';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import { vehicleApi } from '../../services/api';
import { createBooking } from '../../store/slices/vehicleSlice';

function VehicleBookingCreate() {
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();
    const [vehicles, setVehicles] = useState([]);
    const [loading, setLoading] = useState(false);
    const [availability, setAvailability] = useState(null);
    const [form, setForm] = useState({
        vehicle_id: searchParams.get('vehicle_id') || '',
        purpose: '', destination: '', start_datetime: '', end_datetime: '',
        passengers: 1, estimated_distance: '', notes: ''
    });

    useEffect(() => {
        vehicleApi.getAll({ status: 'available' }).then(res => setVehicles(res.data.data));
    }, []);

    const handleChange = (e) => { setForm({ ...form, [e.target.name]: e.target.value }); setAvailability(null); };

    const checkAvailability = async () => {
        if (!form.vehicle_id || !form.start_datetime || !form.end_datetime) {
            toast.warning('Pilih kendaraan dan waktu terlebih dahulu.');
            return;
        }
        try {
            const res = await vehicleApi.checkAvailability({ vehicle_id: form.vehicle_id, start_datetime: form.start_datetime, end_datetime: form.end_datetime });
            setAvailability(res.data.data.available);
            toast[res.data.data.available ? 'success' : 'error'](res.data.data.available ? 'Kendaraan tersedia!' : 'Kendaraan tidak tersedia. Terdapat bentrok jadwal.');
        } catch (err) { toast.error('Gagal mengecek ketersediaan.'); }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            await dispatch(createBooking(form)).unwrap();
            toast.success('Booking kendaraan berhasil diajukan.');
            navigate('/vehicle-bookings/my');
        } catch (err) { toast.error(err || 'Gagal membuat booking.'); }
        setLoading(false);
    };

    return (
        <div className="container-fluid">
            <div className="box box-primary">
                <div className="box-header with-border"><h3 className="box-title"><i className="fa fa-car"></i> Booking Kendaraan</h3></div>
                <form onSubmit={handleSubmit}>
                    <div className="box-body">
                        <div className="row">
                            <div className="col-md-6">
                                <div className="form-group"><label>Kendaraan *</label><select name="vehicle_id" className="form-control" value={form.vehicle_id} onChange={handleChange} required><option value="">Pilih Kendaraan</option>{vehicles.map(v => <option key={v.id} value={v.id}>{v.name} ({v.plate_number}) - {v.capacity} org</option>)}</select></div>
                                <div className="form-group"><label>Tujuan *</label><input type="text" name="destination" className="form-control" value={form.destination} onChange={handleChange} required placeholder="e.g., Kantor Pusat Jakarta" /></div>
                                <div className="form-group"><label>Keperluan *</label><textarea name="purpose" className="form-control" rows="3" value={form.purpose} onChange={handleChange} required placeholder="Jelaskan keperluan..." /></div>
                            </div>
                            <div className="col-md-6">
                                <div className="form-group"><label>Waktu Mulai *</label><input type="datetime-local" name="start_datetime" className="form-control" value={form.start_datetime} onChange={handleChange} required /></div>
                                <div className="form-group"><label>Waktu Selesai *</label><input type="datetime-local" name="end_datetime" className="form-control" value={form.end_datetime} onChange={handleChange} required /></div>
                                <div className="form-group"><label>Penumpang *</label><input type="number" name="passengers" className="form-control" value={form.passengers} onChange={handleChange} required min="1" /></div>
                                <div className="form-group"><label>Estimasi Jarak (km)</label><input type="number" name="estimated_distance" className="form-control" value={form.estimated_distance} onChange={handleChange} min="0" step="0.1" /></div>
                                <div className="form-group"><label>Catatan</label><textarea name="notes" className="form-control" rows="2" value={form.notes} onChange={handleChange} /></div>
                            </div>
                        </div>
                        <div style={{ marginTop: 10 }}>
                            <button type="button" onClick={checkAvailability} className="btn btn-info btn-sm"><i className="fa fa-check-circle"></i> Cek Ketersediaan</button>
                            {availability !== null && (
                                <span className={availability ? 'text-green' : 'text-red'} style={{ marginLeft: 10 }}>
                                    <i className={`fa ${availability ? 'fa-check' : 'fa-times'}`}></i> {availability ? 'Tersedia' : 'Tidak Tersedia'}
                                </span>
                            )}
                        </div>
                    </div>
                    <div className="box-footer">
                        <button type="button" onClick={() => navigate('/vehicles')} className="btn btn-default"><i className="fa fa-arrow-left"></i> Kembali</button>
                        <button type="submit" className="btn btn-success pull-right" disabled={loading}>{loading ? <i className="fa fa-spinner fa-spin"></i> : <i className="fa fa-paper-plane"></i>} Ajukan Booking</button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default VehicleBookingCreate;