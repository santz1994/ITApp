import Button from '@mui/material/Button';
import Stack from '@mui/material/Stack';

export default function FormButtons({ onCancel, onSubmit, cancelLabel = 'Cancel', submitLabel = 'Save', submitting = false }) {
  return (
    <Stack direction="row" spacing={1} justifyContent="flex-end">
      {onCancel && <Button onClick={onCancel}>{cancelLabel}</Button>}
      <Button variant="contained" type={onSubmit ? 'button' : 'submit'} onClick={onSubmit} disabled={submitting}>
        {submitLabel}
      </Button>
    </Stack>
  );
}
