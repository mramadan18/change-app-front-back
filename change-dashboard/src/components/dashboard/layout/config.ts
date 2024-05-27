import type { NavItemConfig } from '@/types/nav';
import { paths } from '@/paths';

export const navItems = [
  { key: 'overview', title: 'Overview', href: paths.dashboard.overview, icon: 'chart-pie' },
  { key: 'users', title: 'Users', href: paths.dashboard.users, icon: 'users' },
  { key: 'categories', title: 'Categories', href: paths.dashboard.categories, icon: 'plugs-connected' },
  { key: 'volunteers', title: 'Volunteers', href: paths.dashboard.volunteers, icon: 'gear-six' },
  { key: 'error', title: 'Error', href: paths.errors.notFound, icon: 'x-square' },
] satisfies NavItemConfig[];
