import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { toast } from 'react-toastify';
import { inventoryApi } from '../../services/api';

function InventoryRequests() {
    const [requests, setRequests] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('');

    useEffect(() => {
        const params = filter ? { status: filter } : {};
        inventoryApi.getRequests(params).then(res => { setRequests(res.data.data); setLoading(false); }).catch(() => { toast.error('Gagal memuat data.'); setLoading(false); });
    }, [filter]);

    const statusBadge = { pending: 'warning', approved: 'success', rejected: 'danger', partially_fulfilled: 'info', fulfilled: 'primary', cancelled: 'secondary' };

    const handleApprove = async (id) => {
        try { await inventoryApi.approveRequest(id); toast.success('Request disetujui.'); setRequests(requests.map(r => r.id === id ? { ...r, status: 'approved' } : r)); } catch (err) { toast.error(err.response?.data?.message || 'Gagal.'); }
    };

    const handleReject = async (id) => {
        const reason = prompt('Alasan penolakan:');
        if (reason === null) return;
        try { await inventoryApi.rejectRequest(id, { rejection_reason: reason }); toast.success('Request ditolak.'); setRequests(requests.map(r => r.id === id ? { ...r, status: 'rejected' } : r)); } catch (err) { toast.error(err.response?.data?.message || 'Gagal.'); }
    };

    const handleCancel = async (id) => {
        if (!window.confirm('Yakin batalkan request ini?')) return;
        try { await inventoryApi.cancelRequest(id); toast.success('Request dibatalkan.'); setRequests(requests.map(r => r.id === id ? { ...r, status: 'cancelled' } : r)); } catch (err) { toast.error(err.response?.data?.message || 'Gagal.'); }
    };

    return (
        <div className="container-fluid">
            <div className="box box-primary">
                <div className="box-header with-border">
                    <h3 className="box-title"><i className="fa fa-file-alt"></i> Daftar Request Inventaris</h3>
                    <div className="box-tools pull-right"><Link to="/inventory-requests/create" className="btn btn-success btn-sm"><i className="fa fa-plus"></i> Buat Request</Link></div>
                </div>
                <div className="box-body">
                    <div className="form-inline" style={{ marginBottom: 15 }}>
                        <select className="form-control" value={filter} onChange={e => setFilter(e.target.value)}>
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="fulfilled">Fulfilled</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    {loading ? <div className="text-center"><i className="fa fa-spinner fa-spin fa-3x"></i></div> : (
                        <div className="table-responsive">
                            <table className="table table-hover">
                                <thead><tr><th>No</th><th>Tanggal</th><th>Pemohon</th><th>Dept</th><th>Jumlah Item</th><th>Status</th><th>Aksi</th></tr></thead>
                                <tbody>
                                    {requests.map(req => (
                                        <tr key={req.id}>
                                            <td>{req.request_number}</td>
                                            <td>{new Date(req.created_at).toLocaleDateString('id-ID')}</td>
                                            <td>{req.requester?.name || '-'}</td>
                                            <td>{req.department?.name || '-'}</td>
                                            <td>{req.items?.length || 0} item</td>
                                            <td><span className={`label label-${statusBadge[req.status]}`}>{req.status.replace('_', ' ')}</span></td>
                                            <td>
                                                <Link to={`/inventory-requests/${req.id}`} className="btn btn-xs btn-default"><i className="fa fa-eye"></i></Link>
                                                {req.status === 'pending' && <>
                                                    <button onClick={() => handleApprove(req.id)} className="btn btn-xs btn-success" style={{ marginLeft: 3 }}><i className="fa fa-check"></i></button>
                                                    <button onClick={() => handleReject(req.id)} className="btn btn-xs btn-danger" style={{ marginLeft: 3 }}><i className="fa fa-times"></i></button>
                                                    <button onClick={() => handleCancel(req.id)} className="btn btn-xs btn-default" style={{ marginLeft: 3 }}><i className="fa fa-ban"></i></button>
                                                </>}
                                            </td>
                                        </tr>
                                    ))}
                                    {requests.length === 0 && <tr><td colSpan="7" className="text-center text-muted" style={{ padding: 30 }}>Belum ada request inventaris.</td></tr>}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default InventoryRequests;