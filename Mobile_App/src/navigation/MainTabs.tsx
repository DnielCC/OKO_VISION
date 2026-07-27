import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { IconButton } from 'react-native-paper';
import { OKO_COLORS } from '../theme';
import HomeScreen from '../screens/HomeScreen';
import VehiclesScreen from '../screens/VehiclesScreen';
import QRScreen from '../screens/QRScreen';
import HistoryScreen from '../screens/HistoryScreen';
import ProfileScreen from '../screens/ProfileScreen';
import { MainTabsParamList } from '../types';
import { StyleSheet, View } from 'react-native';

const Tabs = createBottomTabNavigator<MainTabsParamList>();

const TabIcon: React.FC<{ icon: string; focused: boolean; color: string; size?: number }> = ({
  icon, focused, color, size = 24,
}) => (
  <View style={[styles.iconWrap, focused && styles.iconWrapFocused]}>
    <IconButton
      icon={icon}
      iconColor={color}
      size={size}
      mode="contained"
      style={{
        margin: 0,
        backgroundColor: focused ? 'rgba(0,242,255,0.15)' : 'transparent',
      }}
      containerColor="transparent"
    />
  </View>
);

const MainTabs: React.FC = () => (
  <Tabs.Navigator
    screenOptions={({ route }) => ({
      headerShown: false,
      tabBarStyle: {
        backgroundColor: OKO_COLORS.bgSecondary,
        borderTopColor: OKO_COLORS.border,
        borderTopWidth: 1,
        height: 72,
        paddingBottom: 8,
        paddingTop: 8,
      },
      tabBarActiveTintColor: OKO_COLORS.accentCyan,
      tabBarInactiveTintColor: OKO_COLORS.textSecondary,
      tabBarLabelStyle: { fontSize: 11, fontWeight: '600' },
      tabBarIcon: ({ color, focused }) => {
        const map: Record<string, string> = {
          Home: 'view-dashboard',
          Vehicles: 'car',
          QR: 'qrcode-scan',
          History: 'history',
          Profile: 'account-circle',
        };
        return <TabIcon icon={map[route.name] || 'help'} focused={focused} color={color} />;
      },
    })}
  >
    <Tabs.Screen name="Home" options={{ title: 'Inicio' }} component={HomeScreen} />
    <Tabs.Screen name="Vehicles" options={{ title: 'Vehículos' }} component={VehiclesScreen} />
    <Tabs.Screen name="QR" options={{ title: 'QR' }} component={QRScreen} />
    <Tabs.Screen name="History" options={{ title: 'Historial' }} component={HistoryScreen} />
    <Tabs.Screen name="Profile" options={{ title: 'Perfil' }} component={ProfileScreen} />
  </Tabs.Navigator>
);

const styles = StyleSheet.create({
  iconWrap: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  iconWrapFocused: {
    transform: [{ translateY: -2 }],
  },
});

export default MainTabs;
