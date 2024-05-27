'use client';

import { useState } from 'react';
import { baseUrl } from '@/api';
import { Button, ButtonGroup, TextField } from '@mui/material';
import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import Stack from '@mui/material/Stack';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import Typography from '@mui/material/Typography';

export function CustomersTable({ customers, getUsers }: any): React.JSX.Element {
  const [points, setPoints] = useState(200);
  const handleChangeRole = async (id: any, type: any) => {
    const token = localStorage.getItem('token');
    const headers = {
      authorization: token ? `Bearer ${token}` : '',
    };

    await baseUrl.put(`/admin/cange_type_user/${id}?type_user=${type}`, {}, { headers });
    getUsers();
  };

  const handleChangeStatus = async (id: any, status: any) => {
    const token = localStorage.getItem('token');
    const headers = {
      authorization: token ? `Bearer ${token}` : '',
    };

    await baseUrl.put(`/admin/cange_status_user/${id}?status=${status}`, {}, { headers });
    getUsers();
  };

  const handleChangePoints = async (e: any) => {
    const points: any = Number(e.target.value);
    if (!Number.isNaN(points)) {
      setPoints(points);
    }
  };

  const handleAddPoints = async (id: any) => {
    const token = localStorage.getItem('token');
    const headers = {
      authorization: token ? `Bearer ${token}` : '',
    };

    await baseUrl.post(`/admin/Add_Point__User?user_id=${id}&points=${points}`, {}, { headers });
    getUsers();
  };

  const handleDeletePoints = async (id: any) => {
    const token = localStorage.getItem('token');
    const headers = {
      authorization: token ? `Bearer ${token}` : '',
    };

    await baseUrl.post(`/admin/Decrese_Point__User?user_id=${id}&points=${points}`, {}, { headers });
    getUsers();
  };

  return (
    <Card>
      <Box sx={{ overflowX: 'auto' }}>
        <Table sx={{ minWidth: '800px' }}>
          <TableHead>
            <TableRow>
              <TableCell>Name</TableCell>
              <TableCell>Email</TableCell>
              <TableCell>Type user</TableCell>
              <TableCell>Status user</TableCell>
              <TableCell>Points</TableCell>
              <TableCell>Add/Remove points</TableCell>
              <TableCell>Actions</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {customers?.map((customer: any) => {
              return (
                <TableRow hover key={customer?.id}>
                  <TableCell>
                    <Stack sx={{ alignItems: 'center' }} direction="row" spacing={2}>
                      <Typography variant="subtitle2">{customer?.name}</Typography>
                    </Stack>
                  </TableCell>
                  <TableCell>{customer?.email}</TableCell>
                  <TableCell>{customer?.type_user == 0 ? 'user' : 'admin'}</TableCell>
                  <TableCell>{customer?.status == 0 ? 'active' : 'block'}</TableCell>
                  <TableCell>{customer?.point}</TableCell>
                  <TableCell>
                    <TextField
                      id="outlined-basic"
                      label="Enter count points"
                      variant="outlined"
                      onChange={handleChangePoints}
                      size="small"
                    />
                    <ButtonGroup variant="contained">
                      <Button color="secondary" onClick={() => handleAddPoints(customer?.id)}>
                        Add points
                      </Button>
                      <Button color="error" onClick={() => handleDeletePoints(customer?.id)}>
                        Delete points
                      </Button>
                    </ButtonGroup>
                  </TableCell>
                  <TableCell>
                    <ButtonGroup size="small" variant="contained">
                      <Button onClick={() => handleChangeRole(customer?.id, customer?.type_user == 0 ? '1' : '0')}>
                        {customer?.type_user == 0 ? 'Admin' : 'User'}
                      </Button>
                      <Button onClick={() => handleChangeStatus(customer?.id, customer?.status == 0 ? '1' : '0')}>
                        {customer?.status == 0 ? 'Block' : 'Unblock'}
                      </Button>
                    </ButtonGroup>
                  </TableCell>
                </TableRow>
              );
            })}
          </TableBody>
        </Table>
      </Box>
    </Card>
  );
}
