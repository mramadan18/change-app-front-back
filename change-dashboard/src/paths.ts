export const paths = {
  home: '/',
  auth: { signIn: '/auth/sign-in', signUp: '/auth/sign-up', resetPassword: '/auth/reset-password' },
  dashboard: {
    overview: '/dashboard',
    account: '/dashboard/account',
    users: '/dashboard/users',
    categories: '/dashboard/categories',
    volunteers: '/dashboard/volunteers',
  },
  errors: { notFound: '/errors/not-found' },
} as const;
