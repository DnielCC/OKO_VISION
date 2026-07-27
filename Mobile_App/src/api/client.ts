import axios, { AxiosError, AxiosInstance, InternalAxiosRequestConfig } from 'axios';
import { storage } from '../utils/storage';
import Constants from 'expo-constants';
import { Platform } from 'react-native';

const getDefaultBaseURL = () => {
  if (typeof process !== 'undefined' && process.env.EXPO_PUBLIC_API_URL) {
    return process.env.EXPO_PUBLIC_API_URL;
  }
  const FLASK_PORT = 5000;
  if (Platform.OS === 'android') {
    return `http://10.0.2.2:${FLASK_PORT}/api`;
  }
  if (Platform.OS === 'ios') {
    return `http://127.0.0.1:${FLASK_PORT}/api`;
  }
  return `http://localhost:${FLASK_PORT}/api`;
};

const baseURL = getDefaultBaseURL();

const api: AxiosInstance = axios.create({
  baseURL,
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

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
      // token expired o inválido, limpiar
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
      return data.detail.map((d: any) => d?.msg || String(d)).join('\n');
    }
    if (error.code === 'ECONNABORTED') return 'Tiempo de espera agotado. Revisa tu conexión.';
    if (error.code === 'ERR_NETWORK' || !error.response) {
      return 'Sin conexión con el servidor. Verifica la API_URL.';
    }
    if (error.response.status === 401) return 'Credenciales inválidas o sesión expirada';
    if (error.response.status === 403) return 'No tienes permisos para esta acción';
    if (error.response.status === 404) return 'Recurso no encontrado';
    return `Error ${error.response?.status || ''}: ${error.message}`;
  }
  if (error instanceof Error) return error.message;
  return fallback;
};

export default api;
