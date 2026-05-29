import { useEffect, useState } from 'react';
import { useDispatch } from 'react-redux';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import { vehicleApi } from '../../services/api';
import { createVehicle, updateVehicle } from '../../store/slices/vehicleSlice';

function VehicleCreate() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);
    const [form, setForm] = useState({
        name: '', plate_number: '', brand: '', model: '', year: '', color: '',
        capacity: 4, fuel_type: '', insurance_expiry: '', stnk_expiry: '', notes: ''
    });

    useEffect(() => {
        if (id) {
            vehicleApi.getById(id).then(res => {
                const v = res.data.data;
                setForm({
                    name: v.name || '', plate_number: v.plate_number || '', brand: v.brand || '',
                    model: v.model || '', year: v.year || '', color: v.color || '',
                    capacity: v.capacity || 4, fuel_type: v.fuel_type || '',
                    insurance_expiry: v.insurance_expiry || '', stnk_expiry: v.stnk_expiry || '',
                    notes: v.notes || ''
                });
            });
        }
    }, [id]);

    const handleChange = (e) => {
        setForm({ ...form, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            if (id) {
                await dispatch(updateVehicle({ id, data: form })).unwrap();
                toast.success('Kendaraan berhasil diperbarui.');
            } else {
                await dispatch(createVehicle(form)).unwrap();
                toast.success('Kendaraan berhasil ditambahkan.');
            }
            navigate('/vehicles');
        } catch (err) {
            toast.error(err || 'Terjadi kesalahan.');
        }
        setLoading(false);
    };

    return (
        <div className="container-fluid">
            <div className="box box-primary">
                <div className="box-header with-border">
                    <h3 className="box-title">{id ? 'Edit' : 'Tambah'} Kendaraan</h3>
                </div>
                <form onSubmit={handleSubmit}>
                    <div className="box-body">
                        <div className="row">
                            <div className="col-md-6">
                                <div className="form-group">
                                    <label>Nama Kendaraan *</label>
                                    <input type="text" name="name" className="form-control" value={form.name} onChange={handleChange} required />
                                </div>
                                <div className="form-group">
                                    <label>Nomor Plat *</label>
                                    <input type="text" name="plate_number" className="form-control" value={form.plate_number} onChange={handleChange} required />
                                </div>
                                <div className="form-group">
                                    <label>Merek *</label>
                                    <input type="text" name="brand" className="form-control" value={form.brand} onChange={handleChange} required />
                                </div>
                                <div className="form-group">
                                    <label>Model *</label>
                                    <input type="text" name="model" className="form-control" value={form.model} onChange={handleChange} required />
                                </div>
                                <div className="form-group">
                                    <label>Tahun</label>
                                    <input type="number" name="year" className="form-control" value={form.year} onChange={handleChange} />
                                </div>
                            </div>
                            <div className="col-md-6">
                                <div className="form-group">
                                    <label>Warna</label>
                                    <input type="text" name="color" className="form-control" value={form.color} onChange={handleChange} />
                                </div>
                                <div className="form-group">
                                    <label>Kapasitas *</label>
                                    <input type="number" name="capacity" className="form-control" value={form.capacity} onChange={handleChange} required min="1" />
                                </div>
                                <div className="form-group">
                                    <label>Bahan Bakar</label>
                                    <select name="fuel_type" className="form-control" value={form.fuel_type} onChange={handleChange}>
                                        <option value="">Pilih</option>
                                        <option value="Bensin">Bensin</option>
                                        <option value="Diesel">Diesel</option>
                                        <option value="Hybrid">Hybrid</option>
                                        <option value="Listrik">Listrik</option>
                                    </select>
                                </div>
                                <div className="form-group">
                                    <label>STNK Exp</label>
                                    <input type="date" name="stnk_expiry" className="form-control" value={form.stnk_expiry} onChange={handleChange} />
                                </div>
                                <div className="form-group">
                                    <label>Asuransi Exp</label>
                                    <input type="date" name="insurance_expiry" className="form-control" value={form.insurance_expiry} onChange={handleChange} />
                                </div>
                            </div>
                        </div>
                        <div className="form-group">
                            <label>Catatan</label>
                            <textarea name="notes" className="form-control" rows="3" value={form.notes} onChange={handleChange}></textarea>
                        </div>
                    </div>
                    <div className="box-footer">
                        <button type="button" onClick={() => navigate('/vehicles')} className="btn btn-default"><i className="fa fa-arrow-left"></i> Kembali</button>
                        <button type="submit" className="btn btn-success pull-right" disabled={loading}>
                            {loading ? <i className="fa fa-spinner fa-spin"></i> : <i className="fa fa-save"></i>} Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default VehicleCreate;