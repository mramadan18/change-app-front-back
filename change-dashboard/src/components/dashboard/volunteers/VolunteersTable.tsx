import * as React from 'react';
import { baseUrl } from '@/api';
import { Button, Paper } from '@mui/material';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';

const VolunteersTable = ({ volunteers, getVolunteers }: any) => {
  return (
    <TableContainer component={Paper}>
      <Table sx={{ minWidth: 650 }}>
        <TableHead>
          <TableRow>
            <TableCell>Description</TableCell>
            <TableCell align="right">Start date</TableCell>
            <TableCell align="right">End date</TableCell>
            <TableCell align="right">Count worker</TableCell>
            <TableCell align="right">Category_id</TableCell>
            <TableCell align="right">Point</TableCell>
            <TableCell align="right">Address</TableCell>
            <TableCell align="right">Days</TableCell>
            <TableCell align="right">Status</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {volunteers.map((volunteer: any, index: number) => (
            <TableRow key={index} sx={{ '&:last-child td, &:last-child th': { border: 0 } }}>
              <TableCell component="th" scope="row">
                {volunteer?.description}
              </TableCell>
              <TableCell align="right">{volunteer?.start_date}</TableCell>
              <TableCell align="right">{volunteer?.end_date}</TableCell>
              <TableCell align="right">{volunteer?.count_worker}</TableCell>
              <TableCell align="right">{volunteer?.category_id}</TableCell>
              <TableCell align="right">{volunteer?.point}</TableCell>
              <TableCell align="right">{volunteer?.address}</TableCell>
              <TableCell align="right">{volunteer?.days.join(', ')}</TableCell>
              <TableCell align="right">{volunteer?.status}</TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </TableContainer>
  );
};

export default VolunteersTable;

// "description": "333333333333333333",
// "start_date": "2023-07-04",
// "end_date": "2023-07-07",
// "count_worker": 10,
// "category_id": 1,
// "point": "10.00",
// "address": "aleppo",
// "days": [
//     "sunday",
//     "tuesday"
// ],
// "status": "pending"
