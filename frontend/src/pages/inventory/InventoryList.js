import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Link } from 'react-router-dom';
import { fetchItems } from '../../store/slices/inventorySlice';

function InventoryList() {
    const dispatch = useDispatch();
    const { items, categories, stats, loading } = useSelector((state) => state.inventory);
    const [filters, setFilters] = useState({ search: '', category_id: '', low_stock: false });

    useEffect(() => {
        dispatch(fetchItems(filters));
    }, [dispatch, filters]);

    return (
        <div className="container-fluid">
            <div className="row">
                <div className="col-md-3 col-sm-6"><div className="info-box bg-aqua"><span className="info-box-icon"><i className="fa fa-cubes"></i></span><div className="info-box-content"><span className="info-box-text">Total Item</span><span className="info-box-number">{stats.total_items || 0}</span></div></div></div>
                <div className="col-md-3 col-sm-6"><div className="info-box bg-green"><span className="info-box-icon"><i className="fa fa-tags"></i></span><div className="info-box-content"><span className="info-box-text">Kategori</span><span className="info-box-number">{stats.total_categories || 0}</span></div></div></div>
                <div className="col-md-3 col-sm-6"><div className="info-box bg-red"><span className="info-box-icon"><i className="fa fa-exclamation-triangle"></i></span><div className="info-box-content"><span className="info-box-text">Stok Rendah</span><span className="info-box-number">{stats.low_stock_items || 0}</span></div></div></div>
                <div className="col-md-3 col-sm-6"><div className="info-box bg-yellow"><span className="info-box-icon"><i className="fa fa-clock-o"></i></span><div className="info-box-content"><span className="info-box-text">Request Pending</span><span className="info-box-number">{stats.pending_requests || 0}</span></div></div></div>
            </div>

            <div className="box box-primary">
                <div className="box-header with-border">
                    <h3 className="box-title"><i className="fa fa-cubes"></i> ATK & Sparepart</h3>
                    <div className="box-tools pull-right">
                        <Link to="/inventory-requests/create" className="btn btn-warning btn-sm" style={{ marginRight: 5 }}><i className="fa fa-file-alt"></i> Buat Request</Link>
                        <Link to="/inventory/create" className="btn btn-success btn-sm"><i className="fa fa-plus"></i> Tambah Barang</Link>
                    </div>
                </div>
                <div className="box-body">
                    <div className="form-inline" style={{ marginBottom: 15 }}>
                        <input type="text" className="form-control" placeholder="Cari barang/SKU..." value={filters.search} onChange={e => setFilters({ ...filters, search: e.target.value })} style={{ marginRight: 10 }} />
                        <select className="form-control" value={filters.category_id} onChange={e => setFilters({ ...filters, category_id: e.target.value })} style={{ marginRight: 10 }}>
                            <option value="">Semua Kategori</option>
                            {categories.map(c => <option key={c.id} value={c.id}>{c.name} ({c.items_count})</option>)}
                        </select>
                        <label className="checkbox-inline"><input type="checkbox" checked={filters.low_stock} onChange={e => setFilters({ ...filters, low_stock: e.target.checked })} /> Stok Rendah</label>
                    </div>

                    {loading ? <div className="text-center"><i className="fa fa-spinner fa-spin fa-3x"></i></div> : (
                        <div className="table-responsive">
                            <table className="table table-hover table-striped">
                                <thead><tr><th>SKU</th><th>Nama</th><th>Kategori</th><th>Stok</th><th>Satuan</th><th>Harga</th><th>Lokasi</th><th>Status</th><th>Aksi</th></tr></thead>
                                <tbody>
                                    {items.map(item => (
                                        <tr key={item.id}>
                                            <td><code>{item.sku}</code></td>
                                            <td><Link to={`/inventory/${item.id}`}>{item.name}</Link></td>
                                            <td><span className="label label-default">{item.category?.name || '-'}</span></td>
                                            <td><strong>{item.current_stock}</strong>{item.is_low_stock && <span className="text-red" style={{ marginLeft: 5 }}><i className="fa fa-exclamation-triangle"></i></span>}</td>
                                            <td>{item.unit}</td>
                                            <td>Rp {Number(item.unit_price).toLocaleString()}</td>
                                            <td>{item.location || '-'}</td>
                                            <td>{item.is_out_of_stock ? <span className="label label-danger">Habis</span> : item.is_low_stock ? <span className="label label-warning">Stok Rendah</span> : <span className="label label-success">Normal</span>}</td>
                                            <td><Link to={`/inventory/${item.id}`} className="btn btn-xs btn-default"><i className="fa fa-eye"></i></Link></td>
                                        </tr>
                                    ))}
                                    {items.length === 0 && <tr><td colSpan="9" className="text-center text-muted" style={{ padding: 30 }}>Belum ada item inventaris.</td></tr>}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default InventoryList;