'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { baseUrl } from '@/api';
import Button from '@mui/material/Button';
import Stack from '@mui/material/Stack';
import Typography from '@mui/material/Typography';
import { Plus as PlusIcon } from '@phosphor-icons/react/dist/ssr/Plus';

import VolunteersTable from '@/components/dashboard/volunteers/VolunteersTable';

export default function Page(): React.JSX.Element {
  const router = useRouter();
  const [volunteers, setVolunteers] = useState<any>([]);

  const getVolunteers = async () => {
    const token = localStorage.getItem('token');
    const headers = {
      authorization: token ? `Bearer ${token}` : '',
    };

    const { data } = await baseUrl.get('/admin/all_volunteer', { headers });
    setVolunteers(data);
  };

  useEffect(() => {
    getVolunteers();
  }, []);
  return (
    <Stack spacing={3}>
      <Stack direction="row" spacing={3}>
        <Stack spacing={1} sx={{ flex: '1 1 auto' }}>
          <Typography variant="h4">Volunteers</Typography>
        </Stack>
      </Stack>
      <VolunteersTable volunteers={volunteers} getVolunteers={getVolunteers} />
    </Stack>
  );
}
