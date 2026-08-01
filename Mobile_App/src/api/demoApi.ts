import type { AxiosRequestConfig, AxiosResponse } from 'axios';
import type { AccessLog, Alert, ApiLoginResponse, User, Vehicle } from '../types';

export const DEMO_MODE_ENABLED =
  typeof process !== 'undefined' && process.env.EXPO_PUBLIC_DEMO_MODE === 'true';

const DEMO_TOKEN = 'oko-demo-token';

const demoUser: User = {
  id: 1,
  username: 'A01234567',
  email: 'admin@okovision.com',
  nombre: 'María Carmen',
  apellidos: 'Atilano García',
  id_rol: 1,
  id_persona: 1,
  id_carrera: 3,
  id_departamento: null,
  activo: true,
};

let demoVehicles: Vehicle[] = [
  {
    id: 1,
    plate: 'ABC-123-A',
    marca: 'Nissan',
    modelo: 'Versa',
    anio: 2022,
    color: 'Blanco',
    tipo: 'auto',
    owner_id: 1,
  },
  {
    id: 2,
    plate: 'MOT-908-Z',
    marca: 'Italika',
    modelo: 'FT150',
    anio: 2024,
    color: 'Negro',
    tipo: 'moto',
    owner_id: 1,
  },
];

let demoAccessLogs: AccessLog[] = [
  {
    id: 1,
    user_id: 1,
    vehicle_plate: 'ABC-123-A',
    type: 'entry',
    timestamp: new Date(Date.now() - 1000 * 60 * 15).toISOString(),
    method: 'qr',
    notes: 'Ingreso autorizado en puerta principal.',
  },
  {
    id: 2,
    user_id: 1,
    vehicle_plate: 'ABC-123-A',
    type: 'exit',
    timestamp: new Date(Date.now() - 1000 * 60 * 60 * 3).toISOString(),
    method: 'manual',
    notes: 'Salida registrada por vigilancia.',
  },
  {
    id: 3,
    user_id: 1,
    vehicle_plate: 'MOT-908-Z',
    type: 'entry',
    timestamp: new Date(Date.now() - 1000 * 60 * 60 * 26).toISOString(),
    method: 'qr',
    notes: 'Lectura correcta del QR institucional.',
  },
];

let demoAlerts: Alert[] = [
  {
    id: 1,
    title: 'Vehículo detectado fuera de horario',
    description: 'Se registró un acceso posterior al horario habitual del usuario.',
    severity: 'HIGH',
    status: 'PENDING',
    vehicle_plate: 'ABC-123-A',
    vehicle_owner: 1,
    location: 'Acceso Norte',
    created_at: new Date(Date.now() - 1000 * 60 * 35).toISOString(),
    image_uri: null,
    notes: 'Pendiente de validación por seguridad.',
  },
  {
    id: 2,
    title: 'Placa parcialmente obstruida',
    description: 'La cámara reportó dificultad para leer la placa durante el acceso.',
    severity: 'MEDIUM',
    status: 'REVIEW',
    vehicle_plate: 'MOT-908-Z',
    vehicle_owner: 1,
    location: 'Cámara Patio B',
    created_at: new Date(Date.now() - 1000 * 60 * 60 * 6).toISOString(),
    image_uri: null,
    notes: 'Se recomienda limpieza del portaplacas.',
  },
];

function ok<T>(config: AxiosRequestConfig, data: T, status = 200): Promise<AxiosResponse<T>> {
  return Promise.resolve({
    data,
    status,
    statusText: 'OK',
    headers: {},
    config,
  } as AxiosResponse<T>);
}

function parseBody(config: AxiosRequestConfig): Record<string, any> {
  const raw = config.data;
  if (!raw) return {};
  if (typeof raw === 'string') {
    try {
      return JSON.parse(raw);
    } catch {
      return {};
    }
  }
  return raw as Record<string, any>;
}

function normalizeUrl(url?: string): string {
  return (url || '').replace(/^https?:\/\/[^/]+/, '');
}

function extractId(path: string): number | null {
  const match = path.match(/\/(\d+)\/?$/);
  return match ? Number(match[1]) : null;
}

export async function handleDemoRequest(config: AxiosRequestConfig): Promise<AxiosResponse<any>> {
  const method = String(config.method || 'get').toUpperCase();
  const path = normalizeUrl(config.url);
  const body = parseBody(config);

  if (method === 'POST' && path === '/auth/login') {
    const loginResponse: ApiLoginResponse = {
      access_token: DEMO_TOKEN,
      token_type: 'bearer',
      id: demoUser.id,
      username: demoUser.username,
      email: demoUser.email,
      nombre: demoUser.nombre,
      apellidos: demoUser.apellidos,
      id_rol: demoUser.id_rol,
      id_persona: demoUser.id_persona,
      id_carrera: demoUser.id_carrera,
      id_departamento: demoUser.id_departamento,
      activo: demoUser.activo,
    };
    return ok(config, loginResponse);
  }

  if (method === 'GET' && path === '/auth/me') {
    return ok(config, demoUser);
  }

  if (method === 'GET' && path === '/vehiculos/') {
    return ok(config, demoVehicles);
  }

  if (method === 'POST' && path === '/vehiculos/') {
    const next: Vehicle = {
      id: Math.max(0, ...demoVehicles.map((v) => v.id || 0)) + 1,
      plate: String(body.plate || '').toUpperCase(),
      marca: String(body.marca || ''),
      modelo: String(body.modelo || ''),
      anio: body.anio ?? null,
      color: body.color ?? null,
      tipo: body.tipo ?? 'auto',
      owner_id: Number(body.owner_id || demoUser.id),
      photo_uri: body.photo_uri ?? null,
      created_at: new Date().toISOString(),
    };
    demoVehicles = [next, ...demoVehicles];
    return ok(config, next, 201);
  }

  if (method === 'PATCH' && path.startsWith('/vehiculos/')) {
    const id = extractId(path);
    const idx = demoVehicles.findIndex((v) => v.id === id);
    if (idx >= 0) {
      demoVehicles[idx] = { ...demoVehicles[idx], ...body };
      return ok(config, demoVehicles[idx]);
    }
    return ok(config, { detail: 'Vehículo no encontrado' }, 404);
  }

  if (method === 'DELETE' && path.startsWith('/vehiculos/')) {
    const id = extractId(path);
    demoVehicles = demoVehicles.filter((v) => v.id !== id);
    return ok(config, { ok: true });
  }

  if (method === 'GET' && path === '/accesos/') {
    return ok(config, demoAccessLogs);
  }

  if (method === 'POST' && path === '/accesos/') {
    const newAccess: AccessLog = {
      id: Math.max(0, ...demoAccessLogs.map((a) => a.id || 0)) + 1,
      user_id: Number(body.user_id || demoUser.id),
      vehicle_plate: body.vehicle_plate || demoVehicles[0]?.plate || null,
      type: body.type === 'exit' ? 'exit' : 'entry',
      timestamp: body.timestamp || new Date().toISOString(),
      method: body.method || 'qr',
      notes: body.notes || 'Acceso registrado en modo demostración.',
    };
    demoAccessLogs = [newAccess, ...demoAccessLogs];
    return ok(config, newAccess, 201);
  }

  if (method === 'GET' && path === '/alerts/') {
    return ok(config, demoAlerts);
  }

  if (method === 'PATCH' && path.startsWith('/alerts/')) {
    const id = extractId(path);
    const idx = demoAlerts.findIndex((a) => a.id === id);
    if (idx >= 0) {
      demoAlerts[idx] = { ...demoAlerts[idx], ...body };
      return ok(config, demoAlerts[idx]);
    }
    return ok(config, { detail: 'Alerta no encontrada' }, 404);
  }

  if (method === 'PATCH' && path.startsWith('/usuarios/')) {
    return ok(config, { ok: true, updated: true });
  }

  if (method === 'GET' && path === '/qr/payload') {
    return ok(config, {
      t: 'okovision_user',
      uid: demoUser.id,
      uname: `${demoUser.nombre} ${demoUser.apellidos}`.trim(),
      email: demoUser.email,
      v: 1,
      ts: Math.floor(Date.now() / 1000),
      plate: demoVehicles[0]?.plate || null,
      sig: 'demo-signature-okovision',
    });
  }

  return ok(config, { detail: `Ruta demo no implementada: ${method} ${path}` }, 404);
}
