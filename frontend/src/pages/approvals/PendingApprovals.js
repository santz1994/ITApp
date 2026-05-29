 import { useEffect, useState } from 'react';
import { toast } from 'react-toastify';
import { approvalApi } from '../../services/api';

function PendingApprovals() {
    const [approvals, setApprovals] = useState([]);
    const [loading, setLoading] = useState(true);
    const [rejectReason, setRejectReason] = useState({});

    useEffect(() => {
        approvalApi.getPending().then(res => { setApprovals(res.data.data); setLoading(false); }).catch(() => { toast.error('Gagal memuat data.'); setLoading(false); });
    }, []);

    const handleApprove = async (id) => {
        try {
            await approvalApi.approve(id, {});
            toast.success('Request disetujui.');
            setApprovals(approvals.filter(a => a.id !== id));
        } catch (err) { toast.error(err.response?.data?.message || 'Gagal menyetujui.'); }
    };

    const handleReject = async (id) => {
        const reason = rejectReason[id];
        if (!reason) { toast.warning('Masukkan alasan penolakan.'); return; }
        try {
            await approvalApi.reject(id, { comments: reason });
            toast.success('Request ditolak.');
            setApprovals(approvals.filter(a => a.id !== id));
        } catch (err) { toast.error(err.response?.data?.message || 'Gagal menolak.'); }
    };

    return (
        <div className="container-fluid">
            <div className="box box-primary">
                <div className="box-header with-border">
                    <h3 className="box-title"><i className="fa fa-check-double"></i> Pending Approvals</h3>
                    <div className="box-tools pull-right"><span className="label label-danger">{approvals.length} pending</span></div>
                </div>
                <div className="box-body">
                    {loading ? <div className="text-center"><i className="fa fa-spinner fa-spin fa-3x"></i></div> : approvals.length === 0 ? (
                        <div className="text-center" style={{ padding: 40 }}>
                            <i className="fa fa-check-circle fa-3x text-green"></i>
                            <p className="text-muted" style={{ marginTop: 15 }}>Tidak ada approval yang menunggu.</p>
                        </div>
                    ) : (
                        approvals.map(approval => (
                            <div key={approval.id} className="box box-widget" style={{ marginBottom: 15 }}>
                                <div className="box-header with-border">
                                    <span className={`label label-${approval.status === 'in_progress' ? 'warning' : 'default'}`} style={{ marginRight: 10 }}>
                                        {approval.status.replace('_', ' ').toUpperCase()}
                                    </span>
                                    <span className="username">
                                        {approval.requestable_type?.split('\\').pop()} #{approval.requestable_id}
                                    </span>
                                    <span className="description">Step {approval.current_step} dari {approval.step_instances?.length || '?'}</span>
                                </div>
                                <div className="box-body">
                                    <div className="progress" style={{ marginBottom: 10 }}>
                                        <div className="progress-bar progress-bar-aqua" style={{ width: `${approval.progress || 0}%` }}>{approval.progress || 0}%</div>
                                    </div>
                                    <div className="row">
                                        {approval.step_instances?.map(step => (
                                            <div key={step.id} className="col-md-3">
                                                <div className={`info-box bg-${step.status === 'approved' ? 'green' : step.status === 'rejected' ? 'red' : step.status === 'skipped' ? 'gray' : 'yellow'}`}>
                                                    <span className="info-box-icon"><i className={`fa fa-${step.status === 'approved' ? 'check' : step.status === 'rejected' ? 'times' : step.status === 'skipped' ? 'forward' : 'clock-o'}`}></i></span>
                                                    <div className="info-box-content">
                                                        <span className="info-box-text">Step {step.step_order}: {step.rule_step?.approval_type || '-'}</span>
                                                        <span className="info-box-number">{step.status}</span>
                                                        {step.approver?.name && <span className="progress-description">oleh {step.approver.name}</span>}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                                <div className="box-footer">
                                    <button onClick={() => handleApprove(approval.id)} className="btn btn-success"><i className="fa fa-check"></i> Setujui</button>
                                    <div className="pull-right" style={{ display: 'flex', gap: 5 }}>
                                        <input type="text" className="form-control" placeholder="Alasan penolakan..." style={{ width: 250 }} value={rejectReason[approval.id] || ''} onChange={e => setRejectReason({ ...rejectReason, [approval.id]: e.target.value })} />
                                        <button onClick={() => handleReject(approval.id)} className="btn btn-danger"><i className="fa fa-times"></i> Tolak</button>
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>
        </div>
    );
}

export default PendingApprovals;