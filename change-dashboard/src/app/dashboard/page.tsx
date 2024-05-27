'use client';

import { useEffect, useState } from 'react';
import { baseUrl } from '@/api';
import {
  Paper,
  Stack,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Typography,
} from '@mui/material';

export default function Page(): React.JSX.Element {
  const [dashboard, setDashboard] = useState<any>({});

  const getDashboard = async () => {
    const token = localStorage.getItem('token');
    const headers = {
      authorization: token ? `Bearer ${token}` : '',
    };

    const { data } = await baseUrl.get('/admin/dashboard', { headers });
    setDashboard(data);
  };

  useEffect(() => {
    getDashboard();
  }, []);

  return (
    <Stack spacing={3}>
      <div>
        <Typography variant="h4">Overview</Typography>
      </div>

      <TableContainer component={Paper}>
        <Table sx={{ minWidth: 650 }} size="small" aria-label="a dense table">
          <TableHead>
            <TableRow>
              <TableCell>All user</TableCell>
              <TableCell align="right">All volunteer</TableCell>
              <TableCell align="right">All company</TableCell>
              <TableCell align="right">All pending volunteers</TableCell>
              <TableCell align="right">All compleated volunteers</TableCell>
              <TableCell align="right">All volunteers</TableCell>
              <TableCell align="right">All blocked users</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            <TableRow sx={{ '&:last-child td, &:last-child th': { border: 0 } }}>
              <TableCell component="th" scope="row">
                {dashboard?.count_all_user}
              </TableCell>
              <TableCell align="right">{dashboard?.count_volunteer}</TableCell>
              <TableCell align="right">{dashboard?.count_company}</TableCell>
              <TableCell align="right">{dashboard?.count_pending_volunteers}</TableCell>
              <TableCell align="right">{dashboard?.count_compleated_volunteers}</TableCell>
              <TableCell align="right">{dashboard?.count_all_volunteers}</TableCell>
              <TableCell align="right">{dashboard?.count_blocked_users}</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </TableContainer>
    </Stack>
  );
}
