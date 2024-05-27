import * as React from 'react';
import { baseUrl } from '@/api';
import Card from '@mui/material/Card';
import InputAdornment from '@mui/material/InputAdornment';
import OutlinedInput from '@mui/material/OutlinedInput';
import { MagnifyingGlass as MagnifyingGlassIcon } from '@phosphor-icons/react/dist/ssr/MagnifyingGlass';

export function CustomersFilters({ setCustomers, getUsers }: any): React.JSX.Element {
  const handleSearch = async (e: any) => {
    const token = localStorage.getItem('token');
    const headers = {
      authorization: token ? `Bearer ${token}` : '',
    };

    if (e.target.value !== '') {
      const { data } = await baseUrl.post(`/admin/find_User_Email?email=${e.target.value}`, {}, { headers });
      setCustomers([data]);
    } else {
      getUsers();
    }
  };
  return (
    <Card sx={{ p: 2 }}>
      <OutlinedInput
        onChange={handleSearch}
        defaultValue=""
        fullWidth
        placeholder="Search customer"
        startAdornment={
          <InputAdornment position="start">
            <MagnifyingGlassIcon fontSize="var(--icon-fontSize-md)" />
          </InputAdornment>
        }
      />
    </Card>
  );
}
