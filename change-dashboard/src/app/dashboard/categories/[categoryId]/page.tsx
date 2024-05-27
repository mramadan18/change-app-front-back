'use client';

import { useState } from 'react';
import { baseUrl } from '@/api';
import { Button, Grid, Stack, TextField, Typography } from '@mui/material';

const EditCategoryPage = ({ params }: any) => {
  const { categoryId } = params;
  const [name, setName] = useState('');
  const [discription, setDiscription] = useState('');

  const handleAddCategory = async () => {
    const token = localStorage.getItem('token');
    const headers = {
      authorization: token ? `Bearer ${token}` : '',
    };

    if (name !== '' && discription !== '') {
      await baseUrl.put(
        `/admin/category_update/${categoryId}?name=${name}&discription=${discription}`,
        {},
        { headers }
      );
      setName('');
      setDiscription('');
    }
  };

  return (
    <Stack spacing={3}>
      <Stack direction="row" spacing={3}>
        <Stack spacing={1} sx={{ flex: '1 1 auto' }}>
          <Typography variant="h4">Edit categories</Typography>
        </Stack>
      </Stack>
      <Grid container mt={5}>
        <Grid item md={6} xs={12}>
          <TextField
            id="outlined-basic"
            label="Enter Name"
            variant="outlined"
            fullWidth
            defaultValue={name}
            onChange={(e) => setName(e.target.value)}
          />
        </Grid>
        <Grid item md={6} xs={12}>
          <TextField
            id="outlined-basic"
            label="Enter description"
            variant="outlined"
            fullWidth
            defaultValue={discription}
            onChange={(e) => setDiscription(e.target.value)}
          />
        </Grid>
        <Grid xs={12} mt={4}>
          <Button
            variant="contained"
            color="success"
            fullWidth
            size="large"
            sx={{ borderRadius: '0' }}
            onClick={handleAddCategory}
            disabled={name === '' || discription === ''}
          >
            Edit Category
          </Button>
        </Grid>
      </Grid>
    </Stack>
  );
};

export default EditCategoryPage;
