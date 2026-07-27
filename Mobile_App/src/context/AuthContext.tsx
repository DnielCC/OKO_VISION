import React, { createContext, useContext, useEffect, useMemo, useState, useCallback } from 'react';
import api, { extractErrorMessage } from '../api/client';
import { storage } from '../utils/storage';
import { ApiLoginResponse, User } from '../types';

interface AuthContextValue {
  user: User | null;
  token: string | null;
  loading: boolean;
  error: string | null;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  updateUser: (patch: Partial<User>) => void;
  restoreSession: () => Promise<void>;
  biometryEnabled: boolean;
  setBiometryEnabled: (on: boolean) => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [biometryEnabled, setBiometryEnabledState] = useState(false);

  const restoreSession = useCallback(async () => {
    try {
      setLoading(true);
      const [t, u, b] = await Promise.all([
        storage.getToken(),
        storage.getUser<User>(),
        storage.isBiometryEnabled(),
      ]);
      setToken(t);
      setUser(u);
      setBiometryEnabledState(b);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    restoreSession();
  }, [restoreSession]);

  const login = useCallback(async (email: string, password: string) => {
    setError(null);
    try {
      const { data } = await api.post<ApiLoginResponse>('/auth/login', { email, password });
      const userObj: User = {
        id: data.id,
        username: data.username,
        email: data.email,
        nombre: data.nombre,
        apellidos: data.apellidos,
        id_rol: data.id_rol,
        id_persona: data.id_persona,
        id_carrera: data.id_carrera,
        id_departamento: data.id_departamento,
        activo: data.activo,
      };
      setToken(data.access_token);
      setUser(userObj);
      await Promise.all([
        storage.setToken(data.access_token),
        storage.setUser<User>(userObj),
      ]);
    } catch (e: unknown) {
      const msg = extractErrorMessage(e, 'Error al iniciar sesión');
      setError(msg);
      throw new Error(msg);
    }
  }, []);

  const logout = useCallback(async () => {
    await storage.clearAll();
    setToken(null);
    setUser(null);
    setError(null);
  }, []);

  const updateUser = useCallback((patch: Partial<User>) => {
    setUser((u) => {
      if (!u) return u;
      const updated = { ...u, ...patch };
      storage.setUser<User>(updated).catch(() => {});
      return updated;
    });
  }, []);

  const setBiometryEnabled = useCallback(async (on: boolean) => {
    await storage.setBiometryEnabled(on);
    setBiometryEnabledState(on);
  }, []);

  const value = useMemo<AuthContextValue>(() => ({
    user, token, loading, error,
    login, logout, updateUser, restoreSession,
    biometryEnabled, setBiometryEnabled,
  }), [user, token, loading, error, login, logout, updateUser, restoreSession, biometryEnabled, setBiometryEnabled]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth debe usarse dentro de AuthProvider');
  return ctx;
};
