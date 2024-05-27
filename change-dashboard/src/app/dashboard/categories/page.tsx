'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { baseUrl } from '@/api';
import Button from '@mui/material/Button';
import Stack from '@mui/material/Stack';
import Typography from '@mui/material/Typography';
import Grid from '@mui/material/Unstable_Grid2';
import { Plus as PlusIcon } from '@phosphor-icons/react/dist/ssr/Plus';

import { IntegrationCard } from '@/components/dashboard/integrations/integrations-card';

export default function Page(): React.JSX.Element {
  const router = useRouter();
  const [categories, setCategories] = useState<any>([]);

  const getCategories = async () => {
    const token = localStorage.getItem('token');
    const headers = {
      authorization: token ? `Bearer ${token}` : '',
    };

    const { data } = await baseUrl.get('/admin/categories_display', { headers });
    setCategories(data);
  };

  useEffect(() => {
    getCategories();
  }, []);
  return (
    <Stack spacing={3}>
      <Stack direction="row" spacing={3}>
        <Stack spacing={1} sx={{ flex: '1 1 auto' }}>
          <Typography variant="h4">Categories</Typography>
        </Stack>
        <div>
          <Button
            startIcon={<PlusIcon fontSize="var(--icon-fontSize-md)" />}
            variant="contained"
            onClick={() => router.push('/dashboard/categories/add')}
          >
            Add
          </Button>
        </div>
      </Stack>
      <Grid container spacing={3}>
        {categories.map((category: any) => (
          <Grid key={category.id} lg={4} md={6} xs={12}>
            <IntegrationCard category={category} getCategories={getCategories} />
          </Grid>
        ))}
      </Grid>
    </Stack>
  );
}
