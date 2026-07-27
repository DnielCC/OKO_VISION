import 'react-native-gesture-handler';
import React, { useEffect } from 'react';
import { StatusBar, StyleSheet, View } from 'react-native';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { NavigationContainer } from '@react-navigation/native';
import { PaperProvider } from 'react-native-paper';
import { AuthProvider } from './src/context/AuthContext';
import { NavigationTheme, PaperTheme } from './src/theme';
import RootNavigator from './src/navigation/RootNavigator';
import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';

Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: true,
  }),
});

export default function App() {
  useEffect(() => {
    const setup = async () => {
      if (Device.isDevice) {
        const { status } = await Notifications.getPermissionsAsync();
        if (status !== 'granted') {
          await Notifications.requestPermissionsAsync();
        }
      }
    };
    setup();
  }, []);

  return (
    <GestureHandlerRootView style={styles.root}>
      <SafeAreaProvider>
        <PaperProvider theme={PaperTheme}>
          <AuthProvider>
            <NavigationContainer theme={NavigationTheme}>
              <View style={styles.app}>
                <StatusBar barStyle="light-content" backgroundColor="#050A18" />
                <RootNavigator />
              </View>
            </NavigationContainer>
          </AuthProvider>
        </PaperProvider>
      </SafeAreaProvider>
    </GestureHandlerRootView>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#050A18' },
  app: { flex: 1, backgroundColor: '#050A18' },
});
