import Button from '@mui/material/Button';

export default function FileUploader({ onChange }) {
  return (
    <div>
      <input type="file" onChange={onChange} />
      <Button variant="outlined" sx={{ ml: 1 }}>Upload</Button>
    </div>
  );
}
