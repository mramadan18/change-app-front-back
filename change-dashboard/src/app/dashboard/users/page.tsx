'use client';

import { useEffect, useState } from 'react';
import { baseUrl } from '@/api';
import Stack from '@mui/material/Stack';
import Typography from '@mui/material/Typography';

import { CustomersFilters } from '@/components/dashboard/customer/customers-filters';
import { CustomersTable } from '@/components/dashboard/customer/customers-table';

export default function Page(): React.JSX.Element {
  const [customers, setCustomers] = useState([]);

  const getUsers = async () => {
    const token = localStorage.getItem('token');
    const headers = {
      authorization: token ? `Bearer ${token}` : '',
    };

    const { data } = await baseUrl.get('/admin/all_user', { headers });
    setCustomers(data);
  };

  useEffect(() => {
    getUsers();
  }, []);

  return (
    <Stack spacing={3}>
      <Stack direction="row" spacing={3}>
        <Stack spacing={1} sx={{ flex: '1 1 auto' }}>
          <Typography variant="h4">Users</Typography>
        </Stack>
      </Stack>
      <CustomersFilters setCustomers={setCustomers} getUsers={getUsers} />
      <CustomersTable customers={customers} getUsers={getUsers} />
    </Stack>
  );
}
