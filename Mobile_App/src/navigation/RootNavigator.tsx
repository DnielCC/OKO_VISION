import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { useAuth } from '../context/AuthContext';
import { ActivityIndicator, StyleSheet, View } from 'react-native';
import { OKO_COLORS } from '../theme';
import LoginScreen from '../screens/LoginScreen';
import MainTabs from './MainTabs';
import VehicleFormScreen from '../screens/VehicleFormScreen';
import QRScannerScreen from '../screens/QRScannerScreen';
import ChangePasswordScreen from '../screens/ChangePasswordScreen';
import AlertDetailScreen from '../screens/AlertDetailScreen';
import type { RootStackParamList } from '../types';

const Stack = createNativeStackNavigator<RootStackParamList>();

const LoadingScreen: React.FC = () => (
  <View style={styles.loading}>
    <ActivityIndicator size="large" color={OKO_COLORS.accentCyan} />
  </View>
);

const RootNavigator: React.FC = () => {
  const { token, loading } = useAuth();

  if (loading) return <LoadingScreen />;

  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: OKO_COLORS.bgSecondary },
        headerTintColor: OKO_COLORS.textPrimary,
        headerTitleStyle: { fontWeight: '700' },
        contentStyle: { backgroundColor: OKO_COLORS.bgPrimary },
      }}
    >
      {!token ? (
        <Stack.Screen
          name="Login"
          component={LoginScreen}
          options={{ headerShown: false }}
        />
      ) : (
        <>
          <Stack.Screen
            name="MainTabs"
            component={MainTabs}
            options={{ headerShown: false }}
          />
          <Stack.Screen
            name="VehicleForm"
            component={VehicleFormScreen}
            options={{ title: 'Vehículo', presentation: 'modal' }}
          />
          <Stack.Screen
            name="QRScanner"
            component={QRScannerScreen}
            options={{ title: 'Escanear QR', presentation: 'fullScreenModal' }}
          />
          <Stack.Screen
            name="ChangePassword"
            component={ChangePasswordScreen}
            options={{ title: 'Cambiar contraseña' }}
          />
          <Stack.Screen
            name="AlertDetail"
            component={AlertDetailScreen}
            options={{ title: 'Detalle de alerta' }}
          />
        </>
      )}
    </Stack.Navigator>
  );
};

const styles = StyleSheet.create({
  loading: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: OKO_COLORS.bgPrimary,
  },
});

export default RootNavigator;
