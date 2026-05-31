import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import CircularProgress from '@mui/material/CircularProgress';
import { approvalApi } from '../../services/api';

export default function ApprovalShow() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    let mounted = true;
    approvalApi.show(id).then((res) => {
      if (!mounted) return;
      setData(res.data.data || res.data || res.data.value);
      setLoading(false);
    }).catch((e) => { setError(e.response?.data?.message || 'Gagal memuat data'); setLoading(false); });
    return () => { mounted = false; };
  }, [id]);

  const handleApprove = async () => {
    try { await approvalApi.approve(id, {}); navigate('/approvals'); }
    catch (e) { setError(e.response?.data?.message || 'Gagal approve'); }
  };

  const handleReject = async () => {
    try { await approvalApi.reject(id, {}); navigate('/approvals'); }
    catch (e) { setError(e.response?.data?.message || 'Gagal reject'); }
  };

  if (loading) return <Box sx={{ display: 'flex', justifyContent: 'center', mt: 6 }}><CircularProgress /></Box>;

  return (
    <Box>
      <Typography variant="h5" gutterBottom>Approval Detail</Typography>
      {error && <Typography color="error">{error}</Typography>}
      <pre style={{ background: '#f6f8fa', padding: 12 }}>{JSON.stringify(data, null, 2)}</pre>
      <Box sx={{ display: 'flex', gap: 2, mt: 2 }}>
        <Button variant="contained" color="primary" onClick={handleApprove}>Approve</Button>
        <Button variant="outlined" color="error" onClick={handleReject}>Reject</Button>
      </Box>
    </Box>
  );
}
