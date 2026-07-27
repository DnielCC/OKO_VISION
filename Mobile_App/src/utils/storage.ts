import AsyncStorage from '@react-native-async-storage/async-storage';

const KEYS = {
  TOKEN: 'oko_token',
  USER: 'oko_user',
  BIOMETRY: 'oko_biometry_enabled',
  THEME: 'oko_theme',
} as const;

export const storage = {
  async setToken(token: string) {
    await AsyncStorage.setItem(KEYS.TOKEN, token);
  },
  async getToken(): Promise<string | null> {
    return AsyncStorage.getItem(KEYS.TOKEN);
  },
  async removeToken() {
    await AsyncStorage.removeItem(KEYS.TOKEN);
  },

  async setUser<T = unknown>(user: T) {
    await AsyncStorage.setItem(KEYS.USER, JSON.stringify(user));
  },
  async getUser<T = unknown>(): Promise<T | null> {
    const raw = await AsyncStorage.getItem(KEYS.USER);
    if (!raw) return null;
    try { return JSON.parse(raw) as T; } catch { return null; }
  },
  async removeUser() {
    await AsyncStorage.removeItem(KEYS.USER);
  },

  async isBiometryEnabled(): Promise<boolean> {
    return (await AsyncStorage.getItem(KEYS.BIOMETRY)) === '1';
  },
  async setBiometryEnabled(on: boolean) {
    await AsyncStorage.setItem(KEYS.BIOMETRY, on ? '1' : '0');
  },

  async clearAll() {
    await AsyncStorage.multiRemove([KEYS.TOKEN, KEYS.USER, KEYS.BIOMETRY]);
  },
};
