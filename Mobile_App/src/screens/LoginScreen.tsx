import React, { useEffect, useState } from 'react';
import {
  View, StyleSheet, Text, Image, KeyboardAvoidingView, TouchableWithoutFeedback,
  Keyboard, Platform, ScrollView,
} from 'react-native';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Button, IconButton, Checkbox, Snackbar, TextInput } from 'react-native-paper';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation } from '@react-navigation/native';
import * as LocalAuthentication from 'expo-local-authentication';
import { OKO_COLORS } from '../theme';
import { loginSchema, LoginSchemaType } from '../utils/validators';
import { useAuth } from '../context/AuthContext';
import FormInput from '../components/FormInput';
import GlassCard from '../components/GlassCard';
import Loading from '../components/Loading';

const LoginScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const { login, biometryEnabled, setBiometryEnabled } = useAuth();
  const { control, handleSubmit, setValue, formState: { errors, isSubmitting } } = useForm<LoginSchemaType>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: '', password: '' },
  });
  const [secure, setSecure] = useState(true);
  const [rememberBio, setRememberBio] = useState(false);
  const [snack, setSnack] = useState<{ visible: boolean; msg: string }>({ visible: false, msg: '' });
  const [bioSupported, setBioSupported] = useState(false);
  const [bioChecking, setBioChecking] = useState(false);

  useEffect(() => {
    (async () => {
      const types = await LocalAuthentication.supportedAuthenticationTypesAsync();
      setBioSupported(types.length > 0);
      if (biometryEnabled && types.length > 0) {
        setBioChecking(true);
        try {
          const result = await LocalAuthentication.authenticateAsync({
            promptMessage: 'Accede a OKO VISION',
            fallbackLabel: 'Usar contraseña',
            cancelLabel: 'Cancelar',
          });
          if (result.success) {
            // credenciales guardadas podrían llenarse aquí; por ahora se avisa al usuario
            setSnack({ visible: true, msg: 'Autenticación biométrica exitosa, ingresa tus credenciales' });
          }
        } finally {
          setBioChecking(false);
        }
      }
    })();
  }, []);

  const onSubmit = async (data: LoginSchemaType) => {
    try {
      await login(data.email.toLowerCase().trim(), data.password);
      if (rememberBio && bioSupported) {
        await setBiometryEnabled(true);
      }
    } catch (e: any) {
      setSnack({ visible: true, msg: e.message || 'Error al iniciar sesión' });
    }
  };

  if (bioChecking) {
    return (
      <SafeAreaView style={styles.safe}>
        <Loading full label="Verificando biometría..." />
      </SafeAreaView>
    );
  }

  return (
    <TouchableWithoutFeedback onPress={Keyboard.dismiss}>
      <SafeAreaView style={styles.safe}>
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          style={{ flex: 1 }}
        >
          <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
            <View style={styles.hero}>
              <View style={styles.logoWrap}>
                <IconButton icon="eye-outline" iconColor={OKO_COLORS.accentCyan} size={68} mode="contained" containerColor="rgba(0,242,255,0.15)" style={styles.logoIcon} />
              </View>
              <Text style={styles.brand}>OKO VISION</Text>
              <Text style={styles.subtitle}>Control inteligente de acceso vehicular</Text>
            </View>

            <GlassCard neon style={styles.card}>
              <Text style={styles.cardTitle}>Iniciar sesión</Text>
              <Text style={styles.cardSub}>Ingresa tus credenciales del instituto</Text>

              <View style={{ marginTop: 18 }}>
                <FormInput
                  control={control}
                  name="email"
                  label="Correo o matrícula"
                  error={errors.email?.message}
                  autoCapitalize="none"
                  keyboardType="email-address"
                  left={<TextInput.Icon icon="email-outline" color={OKO_COLORS.accentCyan} />}
                />
                <FormInput
                  control={control}
                  name="password"
                  label="Contraseña"
                  error={errors.password?.message}
                  secureTextEntry={secure}
                  autoCapitalize="none"
                  left={<TextInput.Icon icon="lock-outline" color={OKO_COLORS.accentCyan} />}
                  right={
                    <TextInput.Icon
                      icon={secure ? 'eye-off-outline' : 'eye-outline'}
                      color={OKO_COLORS.accentCyan}
                      onPress={() => setSecure((s) => !s)}
                    />
                  }
                />
              </View>

              {bioSupported && (
                <View style={styles.checkRow}>
                  <Checkbox
                    status={rememberBio ? 'checked' : 'unchecked'}
                    onPress={() => setRememberBio((v) => !v)}
                    color={OKO_COLORS.accentCyan}
                  />
                  <Text style={styles.checkLabel}>Activar acceso biométrico</Text>
                </View>
              )}

              <Button
                mode="contained"
                onPress={handleSubmit(onSubmit)}
                loading={isSubmitting}
                disabled={isSubmitting}
                style={styles.btn}
                contentStyle={styles.btnContent}
                buttonColor={OKO_COLORS.accentCyan}
                textColor="#041019"
              >
                {isSubmitting ? 'Ingresando...' : 'INGRESAR'}
              </Button>

              {bioSupported && (
                <Button
                  mode="text"
                  icon="fingerprint"
                  style={{ marginTop: 10 }}
                  textColor={OKO_COLORS.accentCyan}
                  onPress={async () => {
                    const r = await LocalAuthentication.authenticateAsync({
                      promptMessage: 'Accede con tu huella / Face ID',
                      fallbackLabel: 'Usar contraseña',
                    });
                    if (r.success) {
                      setSnack({ visible: true, msg: 'Autenticación biométrica OK' });
                      if (biometryEnabled) {
                        // En producción llenaríamos email/pass guardados en secure storage.
                      }
                    } else {
                      setSnack({ visible: true, msg: 'No se pudo autenticar' });
                    }
                  }}
                >
                  Usar huella / Face ID
                </Button>
              )}

              <Text style={styles.hint}>
                Admin demo: admin@okovision.com / 12345678
              </Text>
            </GlassCard>
          </ScrollView>
        </KeyboardAvoidingView>
        <Snackbar
          visible={snack.visible}
          onDismiss={() => setSnack((s) => ({ ...s, visible: false }))}
          duration={3500}
          style={styles.snack}
          action={{
            label: 'OK',
            onPress: () => setSnack((s) => ({ ...s, visible: false })),
          }}
        >
          {snack.msg}
        </Snackbar>
      </SafeAreaView>
    </TouchableWithoutFeedback>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: OKO_COLORS.bgPrimary },
  scroll: { flexGrow: 1, paddingHorizontal: 22, paddingVertical: 28, justifyContent: 'center', gap: 28 },
  hero: { alignItems: 'center', gap: 6 },
  logoWrap: { marginBottom: 10 },
  logoIcon: { width: 110, height: 110, borderRadius: 55 },
  brand: {
    fontSize: 36, fontWeight: '900', color: OKO_COLORS.accentCyan,
    letterSpacing: 2, textShadowColor: 'rgba(0,242,255,0.4)', textShadowRadius: 12,
  },
  subtitle: { color: OKO_COLORS.textSecondary, fontSize: 13, fontWeight: '500' },
  card: { marginTop: 6 },
  cardTitle: { fontSize: 24, fontWeight: '800', color: OKO_COLORS.textPrimary },
  cardSub: { color: OKO_COLORS.textSecondary, marginTop: 4, fontSize: 13 },
  btn: { marginTop: 14, borderRadius: 14 },
  btnContent: { paddingVertical: 10, fontWeight: '800' },
  checkRow: { flexDirection: 'row', alignItems: 'center', marginTop: 10, marginLeft: -6 },
  checkLabel: { color: OKO_COLORS.textSecondary, fontSize: 13, fontWeight: '500' },
  hint: { textAlign: 'center', marginTop: 20, color: OKO_COLORS.textSecondary, fontSize: 11 },
  snack: { backgroundColor: OKO_COLORS.bgSecondary, borderWidth: 1, borderColor: OKO_COLORS.border },
});

export default LoginScreen;
