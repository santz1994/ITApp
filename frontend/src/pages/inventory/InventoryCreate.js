import { useEffect, useState } from 'react';
import { useDispatch } from 'react-redux';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import { inventoryApi } from '../../services/api';
import { createItem, updateItem } from '../../store/slices/inventorySlice';

function InventoryCreate() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(false);
    const [form, setForm] = useState({
        category_id: '', name: '', sku: '', description: '', unit: 'pcs',
        current_stock: 0, minimum_stock: 0, unit_price: 0, location: ''
    });

    useEffect(() => {
        inventoryApi.getCategories().then(res => setCategories(res.data.data));
        if (id) {
            inventoryApi.getById(id).then(res => {
                const item = res.data.data;
                setForm({
                    category_id: item.category_id || '', name: item.name || '', sku: item.sku || '',
                    description: item.description || '', unit: item.unit || 'pcs',
                    current_stock: item.current_stock || 0, minimum_stock: item.minimum_stock || 0,
                    unit_price: item.unit_price || 0, location: item.location || ''
                });
            });
        }
    }, [id]);

    const handleChange = (e) => { setForm({ ...form, [e.target.name]: e.target.value }); };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            if (id) {
                await dispatch(updateItem({ id, data: form })).unwrap();
                toast.success('Item berhasil diperbarui.');
            } else {
                await dispatch(createItem(form)).unwrap();
                toast.success('Item berhasil ditambahkan.');
            }
            navigate('/inventory');
        } catch (err) { toast.error(err || 'Terjadi kesalahan.'); }
        setLoading(false);
    };

    return (
        <div className="container-fluid">
            <div className="box box-primary">
                <div className="box-header with-border"><h3 className="box-title">{id ? 'Edit' : 'Tambah'} Item Inventaris</h3></div>
                <form onSubmit={handleSubmit}>
                    <div className="box-body">
                        <div className="row">
                            <div className="col-md-6">
                                <div className="form-group"><label>Kategori *</label><select name="category_id" className="form-control" value={form.category_id} onChange={handleChange} required><option value="">Pilih Kategori</option>{categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}</select></div>
                                <div className="form-group"><label>Nama Barang *</label><input type="text" name="name" className="form-control" value={form.name} onChange={handleChange} required placeholder="e.g., Kertas A4 80gr" /></div>
                                <div className="form-group"><label>SKU *</label><input type="text" name="sku" className="form-control" value={form.sku} onChange={handleChange} required placeholder="e.g., ATK-001" /></div>
                                <div className="form-group"><label>Satuan *</label><input type="text" name="unit" className="form-control" value={form.unit} onChange={handleChange} required placeholder="pcs, box, rim" /></div>
                                <div className="form-group"><label>Deskripsi</label><textarea name="description" className="form-control" rows="3" value={form.description} onChange={handleChange} /></div>
                            </div>
                            <div className="col-md-6">
                                {!id && <div className="form-group"><label>Stok Awal</label><input type="number" name="current_stock" className="form-control" value={form.current_stock} onChange={handleChange} min="0" /></div>}
                                <div className="form-group"><label>Batas Minimum Stok</label><input type="number" name="minimum_stock" className="form-control" value={form.minimum_stock} onChange={handleChange} min="0" /></div>
                                <div className="form-group"><label>Harga Satuan (Rp)</label><input type="number" name="unit_price" className="form-control" value={form.unit_price} onChange={handleChange} min="0" /></div>
                                <div className="form-group"><label>Lokasi Penyimpanan</label><input type="text" name="location" className="form-control" value={form.location} onChange={handleChange} placeholder="e.g., Gudang A, Rak B3" /></div>
                            </div>
                        </div>
                    </div>
                    <div className="box-footer">
                        <button type="button" onClick={() => navigate('/inventory')} className="btn btn-default"><i className="fa fa-arrow-left"></i> Kembali</button>
                        <button type="submit" className="btn btn-success pull-right" disabled={loading}>{loading ? <i className="fa fa-spinner fa-spin"></i> : <i className="fa fa-save"></i>} Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default InventoryCreate;