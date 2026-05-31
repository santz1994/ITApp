import Chip from '@mui/material/Chip';

const colorMap = {
  success: 'success',
  warning: 'warning',
  error: 'error',
  info: 'info',
  default: 'default',
  secondary: 'secondary',
};

export default function StatusBadge({ label, color = 'default', variant = 'filled' }) {
  return <Chip label={label} color={colorMap[color] || colorMap.default} variant={variant} size="small" />;
}
