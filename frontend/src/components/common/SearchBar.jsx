import TextField from '@mui/material/TextField';

export default function SearchBar({ value, onChange, placeholder = 'Search...' }) {
  return (
    <TextField
      fullWidth
      size="small"
      value={value}
      onChange={(event) => onChange?.(event.target.value)}
      placeholder={placeholder}
    />
  );
}
