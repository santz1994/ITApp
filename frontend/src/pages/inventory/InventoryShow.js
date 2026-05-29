import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import { inventoryApi } from '../../services/api';

function InventoryShow() {
    const { id } = useParams();
    const [item, setItem] = useState(null);
    const [movements, setMovements] = useState([]);
    const [loading, setLoading] = useState(true);
    const [stockForm, setStockForm] = useState({ quantity: '', notes: '' });

    useEffect(() => {
        inventoryApi.getById(id).then(res => {
            setItem(res.data.data);
            setMovements(res.data.stock_movements || []);
            setLoading(false);
        }).catch(() => { toast.error('Gagal memuat data.'); setLoading(false); });
    }, [id]);

    const handleAddStock = async (e) => {
        e.preventDefault();
        try {
            await inventoryApi.addStock(id, stockForm);
            toast.success('Stok berhasil ditambahkan.');
            const res = await inventoryApi.getById(id);
            setItem(res.data.data);
            setMovements(res.data.stock_movements || []);
            setStockForm({ quantity: '', notes: '' });
        } catch (err) { toast.error('Gagal menambah stok.'); }
    };

    const handleReduceStock = async (e) => {
        e.preventDefault();
        try {
            await inventoryApi.reduceStock(id, stockForm);
            toast.success('Stok berhasil dikurangi.');
            const res = await inventoryApi.getById(id);
            setItem(res.data.data);
            setMovements(res.data.stock_movements || []);
            setStockForm({ quantity: '', notes: '' });
        } catch (err) { toast.error(err.response?.data?.message || 'Gagal mengurangi stok.'); }
    };

    if (loading) return <div className="text-center p-5"><i className="fa fa-spinner fa-spin fa-3x"></i></div>;
    if (!item) return <div className="alert alert-danger">Item tidak ditemukan.</div>;

    return (
        <div className="container-fluid">
            <div className="row">
                <div className="col-md-4">
                    <div className="box box-primary">
                        <div className="box-header with-border">
                            <h3 className="box-title">{item.name}</h3>
                            <div className="box-tools pull-right">
                                <Link to={`/inventory/${id}/edit`} className="btn btn-warning btn-xs"><i className="fa fa-edit"></i></Link>
                            </div>
                        </div>
                        <div className="box-body">
                            <ul className="nav nav-stacked">
                                <li><a href="#">SKU <span className="pull-right badge bg-blue">{item.sku}</span></a></li>
                                <li><a href="#">Kategori <span className="pull-right badge bg-purple">{item.category?.name || '-'}</span></a></li>
                                <li><a href="#">Stok Saat Ini <span className="pull-right badge bg-green">{item.current_stock} {item.unit}</span></a></li>
                                <li><a href="#">Batas Minimum <span className="pull-right badge bg-yellow">{item.minimum_stock} {item.unit}</span></a></li>
                                <li><a href="#">Harga Satuan <span className="pull-right badge bg-teal">Rp {Number(item.unit_price).toLocaleString()}</span></a></li>
                                <li><a href="#">Lokasi <span className="pull-right badge bg-gray">{item.location || '-'}</span></a></li>
                            </ul>
                            {item.is_low_stock && <div className="alert alert-warning" style={{ marginTop: 10 }}><i className="fa fa-exclamation-triangle"></i> Stok di bawah batas minimum!</div>}
                            {item.is_out_of_stock && <div className="alert alert-danger" style={{ marginTop: 10 }}><i className="fa fa-times-circle"></i> Stok habis!</div>}
                        </div>
                    </div>

                    <div className="box box-success">
                        <div className="box-header with-border"><h3 className="box-title">Kelola Stok</h3></div>
                        <div className="box-body">
                            <div className="form-group"><label>Jumlah</label><input type="number" className="form-control" value={stockForm.quantity} onChange={e => setStockForm({ ...stockForm, quantity: e.target.value })} min="1" /></div>
                            <div className="form-group"><label>Catatan</label><input type="text" className="form-control" value={stockForm.notes} onChange={e => setStockForm({ ...stockForm, notes: e.target.value })} /></div>
                            <button onClick={handleAddStock} className="btn btn-success"><i className="fa fa-plus"></i> Stok Masuk</button>
                            <button onClick={handleReduceStock} className="btn btn-danger" style={{ marginLeft: 5 }}><i className="fa fa-minus"></i> Stok Keluar</button>
                        </div>
                    </div>
                </div>

                <div className="col-md-8">
                    <div className="box box-primary">
                        <div className="box-header with-border">
                            <h3 className="box-title"><i className="fa fa-history"></i> Riwayat Stok</h3>
                            <div className="box-tools pull-right"><Link to="/inventory" className="btn btn-default btn-sm"><i className="fa fa-arrow-left"></i> Kembali</Link></div>
                        </div>
                        <div className="box-body table-responsive no-padding">
                            <table className="table table-hover">
                                <thead><tr><th>Tanggal</th><th>Tipe</th><th>Jumlah</th><th>Stok Sebelum</th><th>Stok Sesudah</th><th>Catatan</th><th>Oleh</th></tr></thead>
                                <tbody>
                                    {movements.map(m => (
                                        <tr key={m.id}>
                                            <td>{new Date(m.created_at).toLocaleString('id-ID')}</td>
                                            <td><span className={`label label-${m.type === 'in' ? 'success' : m.type === 'out' ? 'danger' : 'info'}`}>{m.type === 'in' ? 'Masuk' : m.type === 'out' ? 'Keluar' : 'Penyesuaian'}</span></td>
                                            <td><strong className={m.type === 'in' ? 'text-green' : 'text-red'}>{m.type === 'in' ? '+' : m.type === 'out' ? '-' : ''}{Math.abs(m.quantity)}</strong></td>
                                            <td>{m.stock_before}</td>
                                            <td>{m.stock_after}</td>
                                            <td>{m.notes || '-'}</td>
                                            <td>{m.recorder?.name || '-'}</td>
                                        </tr>
                                    ))}
                                    {movements.length === 0 && <tr><td colSpan="7" className="text-center text-muted" style={{ padding: 30 }}>Belum ada pergerakan stok.</td></tr>}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default InventoryShow;