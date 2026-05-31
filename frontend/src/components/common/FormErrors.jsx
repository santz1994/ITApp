import Alert from '@mui/material/Alert';
import Stack from '@mui/material/Stack';

export default function FormErrors({ errors = [] }) {
  if (!errors.length) return null;

  return (
    <Stack spacing={1}>
      {errors.map((error, index) => (
        <Alert severity="error" key={`${error}-${index}`}>{error}</Alert>
      ))}
    </Stack>
  );
}
