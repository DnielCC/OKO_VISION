import axios, { AxiosError, AxiosInstance, InternalAxiosRequestConfig } from 'axios';
import { storage } from '../utils/storage';
import Constants from 'expo-constants';
import { Platform } from 'react-native';
import { DEMO_MODE_ENABLED, handleDemoRequest } from './demoApi';

/**
 * Configuración de base URL para la App Móvil OKO VISION.
 *
 * OPCIONES de conexión:
 *   1. EXPO_PUBLIC_API_URL (variable de entorno) - PRIORIDAD MÁXIMA.
 *      Ej: set EXPO_PUBLIC_API_URL=https://192.168.1.50/mobile-api
 *   2. Si es Android Emulador: 10.0.2.2 (alias al host) via /mobile-api al gateway.
 *   3. Si es iOS Simulator: 127.0.0.1
 *   4. Web/Default: localhost
 *
 * NOTA: En producción / dispositivos físicos, debe usarse HTTPS con la IP real del gateway Nginx.
 *       Ej: https://192.168.10.20/mobile-api
 */
const getDefaultBaseURL = () => {
  // Prioridad máxima: usar la URL de AWS configurada en .env
  if (typeof process !== 'undefined' && process.env.EXPO_PUBLIC_API_URL) {
    const rawUrl = process.env.EXPO_PUBLIC_API_URL.trim();
    const envUrl = rawUrl.replace(/\/+$/, '');
    console.log('🔗 Using API URL from .env:', envUrl);
    return envUrl;
  }

  // Fallback para desarrollo local con Expo Go
  const expoHost =
    Constants.expoConfig?.hostUri ??
    (Constants as any)?.manifest2?.extra?.expoGo?.debuggerHost ??
    '';
  const expoIp = typeof expoHost === 'string' ? expoHost.split(':')[0] : '';
  
  if (expoIp && /^\d{1,3}(\.\d{1,3}){3}$/.test(expoIp)) {
    console.log('🔗 Using Expo Go host IP:', expoIp);
    return `http://${expoIp}/mobile-api`;
  }

  // Fallback final para emuladores
  if (Platform.OS === 'android') {
    return 'http://10.0.2.2/mobile-api';
  }
  if (Platform.OS === 'ios') {
    return 'http://127.0.0.1/mobile-api';
  }
  return 'http://localhost/mobile-api';
};

const baseURL = getDefaultBaseURL();

const api: AxiosInstance = axios.create({
  baseURL,
  timeout: 20000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-Requested-With': 'OKO-Vision-Mobile',
  },
});

if (DEMO_MODE_ENABLED) {
  api.defaults.adapter = handleDemoRequest as any;
}

api.interceptors.request.use(async (config: InternalAxiosRequestConfig) => {
  const token = await storage.getToken();
  if (token) {
    config.headers.set?.('Authorization', `Bearer ${token}`);
  }
  return config;
}, (error) => Promise.reject(error));

api.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    if (error.response?.status === 401) {
      // Token expirado o inválido: limpiar sesión
      storage.clearAll().catch(() => {});
    }
    return Promise.reject(error);
  }
);

export const extractErrorMessage = (error: unknown, fallback = 'Error desconocido'): string => {
  if (!error) return fallback;
  if (axios.isAxiosError(error)) {
    const data = error.response?.data as any;
    if (typeof data?.detail === 'string') return data.detail;
    if (Array.isArray(data?.detail)) {
      return data.detail
        .map((d: any) => (typeof d === 'string' ? d : (d?.msg || d?.message || JSON.stringify(d))))
        .join('\n');
    }
    if (typeof data?.error === 'string') return data.error;
    if (error.code === 'ECONNABORTED') return 'Tiempo de espera agotado. Revisa tu conexión.';
    if (error.code === 'ERR_NETWORK' || !error.response) {
      return DEMO_MODE_ENABLED
        ? 'Modo demostración activo. Los datos mostrados son locales.'
        : `Sin conexión con el servidor. Verifica tu conexión y la API URL.\n(${baseURL})`;
    }
    const s = error.response.status;
    if (s === 401) return 'Credenciales inválidas o sesión expirada';
    if (s === 403) return 'No tienes permisos para esta acción';
    if (s === 404) return 'Recurso no encontrado';
    if (s === 422) return 'Datos inválidos. Verifica los campos.';
    if (s === 429) return 'Demasiadas solicitudes. Espera un momento.';
    if (s >= 500) return `Error del servidor (${s}). Intenta más tarde.`;
    return `Error ${s}: ${error.message}`;
  }
  if (error instanceof Error) return error.message;
  return fallback;
};

export default api;
export { DEMO_MODE_ENABLED };
