export interface User {
  id: number;
  username: string;
  email: string;
  nombre?: string;
  apellidos?: string;
  id_rol?: number;
  id_persona?: number;
  id_carrera?: number | null;
  id_departamento?: number | null;
  activo?: boolean;
}

export interface Vehicle {
  id?: number;
  plate: string;
  marca: string;
  modelo: string;
  anio?: number | null;
  color?: string | null;
  tipo?: string | null;
  owner_id: number;
  photo_uri?: string | null;
  created_at?: string;
}

export interface AccessLog {
  id?: number;
  user_id: number;
  vehicle_plate?: string | null;
  type: 'entry' | 'exit';
  timestamp?: string;
  location_lat?: number | null;
  location_lng?: number | null;
  method?: 'qr' | 'plate' | 'manual' | null;
  notes?: string | null;
}

export interface Alert {
  id?: number;
  title: string;
  description?: string;
  severity: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
  status?: string;
  vehicle_plate?: string | null;
  vehicle_owner?: number | string | null;
  location?: string | null;
  created_at?: string;
  image_uri?: string | null;
  notes?: string | null;
}

export interface ApiLoginResponse {
  access_token: string;
  token_type?: string;
  id: number;
  username: string;
  email: string;
  nombre?: string;
  apellidos?: string;
  id_rol?: number;
  id_persona?: number;
  id_carrera?: number | null;
  id_departamento?: number | null;
  activo?: boolean;
}

export type RootStackParamList = {
  Login: undefined;
  MainTabs: undefined;
  VehicleForm: { vehicle?: Vehicle } | undefined;
  QRScanner: undefined;
  AlertDetail: { alert: Alert };
  ChangePassword: undefined;
};

export type MainTabsParamList = {
  Home: undefined;
  Vehicles: undefined;
  QR: undefined;
  History: undefined;
  Profile: undefined;
};
