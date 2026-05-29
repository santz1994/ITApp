import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { inventoryApi } from '../../services/api';

function InventoryRequestCreate() {
    const navigate = useNavigate();
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(false);
    const [notes, setNotes] = useState('');
    const [requestItems, setRequestItems] = useState([{ item_id: '', quantity_requested: 1, notes: '' }]);

    useEffect(() => {
        inventoryApi.getAll().then(res => setItems(res.data.data));
    }, []);

    const addItem = () => setRequestItems([...requestItems, { item_id: '', quantity_requested: 1, notes: '' }]);
    const removeItem = (index) => setRequestItems(requestItems.filter((_, i) => i !== index));
    const updateItem = (index, field, value) => {
        const updated = [...requestItems];
        updated[index][field] = value;
        setRequestItems(updated);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const validItems = requestItems.filter(i => i.item_id && i.quantity_requested > 0);
        if (validItems.length === 0) { toast.warning('Tambahkan minimal 1 item.'); return; }
        setLoading(true);
        try {
            await inventoryApi.createRequest({ notes, items: validItems });
            toast.success('Request inventaris berhasil diajukan.');
            navigate('/inventory-requests');
        } catch (err) { toast.error(err.response?.data?.message || 'Gagal membuat request.'); }
        setLoading(false);
    };

    return (
        <div className="container-fluid">
            <div className="box box-primary">
                <div className="box-header with-border"><h3 className="box-title"><i className="fa fa-file-alt"></i> Buat Request ATK/Sparepart</h3></div>
                <form onSubmit={handleSubmit}>
                    <div className="box-body">
                        <div className="form-group"><label>Catatan</label><textarea className="form-control" rows="2" value={notes} onChange={e => setNotes(e.target.value)} placeholder="Catatan umum request..." /></div>
                        <hr />
                        <h4>Item yang Diminta</h4>
                        <table className="table table-bordered">
                            <thead><tr><th>Barang</th><th>Jumlah</th><th>Catatan</th><th>Aksi</th></tr></thead>
                            <tbody>
                                {requestItems.map((ri, index) => (
                                    <tr key={index}>
                                        <td><select className="form-control" value={ri.item_id} onChange={e => updateItem(index, 'item_id', e.target.value)} required><option value="">Pilih Barang</option>{items.map(i => <option key={i.id} value={i.id}>{i.name} (Stok: {i.current_stock} {i.unit})</option>)}</select></td>
                                        <td><input type="number" className="form-control" value={ri.quantity_requested} onChange={e => updateItem(index, 'quantity_requested', parseInt(e.target.value))} required min="1" style={{ width: 100 }} /></td>
                                        <td><input type="text" className="form-control" value={ri.notes} onChange={e => updateItem(index, 'notes', e.target.value)} placeholder="Opsional" /></td>
                                        <td><button type="button" onClick={() => removeItem(index)} className="btn btn-xs btn-danger"><i className="fa fa-trash"></i></button></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        <button type="button" onClick={addItem} className="btn btn-default btn-sm"><i className="fa fa-plus"></i> Tambah Item</button>
                    </div>
                    <div className="box-footer">
                        <button type="button" onClick={() => navigate('/inventory-requests')} className="btn btn-default"><i className="fa fa-arrow-left"></i> Kembali</button>
                        <button type="submit" className="btn btn-success pull-right" disabled={loading}>{loading ? <i className="fa fa-spinner fa-spin"></i> : <i className="fa fa-paper-plane"></i>} Ajukan Request</button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default InventoryRequestCreate;