import { DarkTheme as NavigationDarkTheme } from '@react-navigation/native';
import { MD3DarkTheme, configureFonts } from 'react-native-paper';

export const OKO_COLORS = {
  bgPrimary: '#050A18',
  bgSecondary: '#0D1B35',
  accentCyan: '#00F2FF',
  accentBlue: '#3B82F6',
  textPrimary: '#E0E6ED',
  textSecondary: '#9CA3AF',
  success: '#00FF87',
  danger: '#FF3131',
  warning: '#FFA500',
  info: '#60A5FA',
  glass: 'rgba(13, 27, 53, 0.7)',
  border: 'rgba(0, 242, 255, 0.12)',
  card: '#101D38',
  input: 'rgba(255,255,255,0.05)',
  critical: '#FF3131',
  high: '#FF7A00',
  medium: '#FFD233',
  low: '#60A5FA',
} as const;

const fontConfig = {
  fontFamily: 'System',
};

export const PaperTheme = {
  ...MD3DarkTheme,
  dark: true,
  colors: {
    ...MD3DarkTheme.colors,
    primary: OKO_COLORS.accentCyan,
    onPrimary: '#041019',
    secondary: OKO_COLORS.accentBlue,
    background: OKO_COLORS.bgPrimary,
    surface: OKO_COLORS.bgSecondary,
    surfaceVariant: OKO_COLORS.card,
    error: OKO_COLORS.danger,
    success: OKO_COLORS.success,
    warning: OKO_COLORS.warning,
    onBackground: OKO_COLORS.textPrimary,
    onSurface: OKO_COLORS.textPrimary,
    onSurfaceVariant: OKO_COLORS.textSecondary,
    outline: OKO_COLORS.border,
  },
  fonts: configureFonts({ config: fontConfig }),
  roundness: 14,
};

export const NavigationTheme = {
  ...NavigationDarkTheme,
  dark: true,
  colors: {
    ...NavigationDarkTheme.colors,
    primary: OKO_COLORS.accentCyan,
    background: OKO_COLORS.bgPrimary,
    card: OKO_COLORS.bgSecondary,
    text: OKO_COLORS.textPrimary,
    border: OKO_COLORS.border,
    notification: OKO_COLORS.danger,
  },
};

export const severityColor = (sev: string) => {
  const s = String(sev || '').toUpperCase();
  if (s === 'CRITICAL') return OKO_COLORS.critical;
  if (s === 'HIGH') return OKO_COLORS.high;
  if (s === 'MEDIUM') return OKO_COLORS.medium;
  return OKO_COLORS.low;
};
