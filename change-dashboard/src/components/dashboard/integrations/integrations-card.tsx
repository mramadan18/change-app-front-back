import { useRouter } from 'next/navigation';
import { baseUrl } from '@/api';
import { Button, ButtonGroup } from '@mui/material';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Divider from '@mui/material/Divider';
import Stack from '@mui/material/Stack';
import Typography from '@mui/material/Typography';

export function IntegrationCard({ category, getCategories }: any): React.JSX.Element {
  const router = useRouter();

  const handleDeleteCategory = async () => {
    const token = localStorage.getItem('token');
    const headers = {
      authorization: token ? `Bearer ${token}` : '',
    };

    await baseUrl.delete(`/admin/category_delete/${category?.id}`, { headers });
    getCategories();
  };

  return (
    <Card sx={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
      <CardContent sx={{ flex: '1 1 auto' }}>
        <Stack spacing={2}>
          <Stack spacing={1}>
            <Typography align="center" variant="h5">
              {category?.name}
            </Typography>
            <Typography align="center" variant="body1">
              {category?.discription}
            </Typography>
          </Stack>
        </Stack>
      </CardContent>
      <Divider />
      <ButtonGroup variant="contained" fullWidth sx={{ borderRadius: '0' }}>
        <Button color="secondary" onClick={() => router.push(`/dashboard/categories/${category.id}`)}>
          Edit
        </Button>
        <Button color="error" onClick={handleDeleteCategory}>
          Delete
        </Button>
      </ButtonGroup>
    </Card>
  );
}
