import { format, formatDistanceToNow, parseISO } from 'date-fns';
import { es } from 'date-fns/locale';

export const fmtFecha = (d?: string | Date | null, fmt: string = 'dd/MM/yyyy HH:mm') => {
  if (!d) return '—';
  try {
    const date = typeof d === 'string' ? parseISO(d) : d;
    return format(date, fmt, { locale: es });
  } catch {
    return String(d);
  }
};

export const fmtRelative = (d?: string | Date | null) => {
  if (!d) return '—';
  try {
    const date = typeof d === 'string' ? parseISO(d) : d;
    return formatDistanceToNow(date, { addSuffix: true, locale: es });
  } catch {
    return String(d);
  }
};

export const fmtNombre = (user?: { nombre?: string; apellidos?: string; username?: string }) => {
  if (!user) return '';
  const full = `${user.nombre || ''} ${user.apellidos || ''}`.trim();
  return full || user.username || 'Usuario';
};

export const fmtTipoVehiculo = (t?: string | null) => {
  if (!t) return 'Otro';
  const map: Record<string, string> = {
    auto: 'Auto', moto: 'Moto', bicicleta: 'Bicicleta',
    camioneta: 'Camioneta', otro: 'Otro',
  };
  return map[t.toLowerCase()] || t.charAt(0).toUpperCase() + t.slice(1);
};

export const fmtTipoAcceso = (t?: string | null) => {
  if (t === 'entry') return { label: 'Entrada', color: '#00FF87', icon: 'arrow-down' };
  if (t === 'exit') return { label: 'Salida', color: '#FF7A00', icon: 'arrow-up' };
  return { label: 'Desconocido', color: '#9CA3AF', icon: 'help' };
};
