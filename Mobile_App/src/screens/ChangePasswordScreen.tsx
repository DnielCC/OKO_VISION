import React, { useState } from 'react';
import {
  View, StyleSheet, Text, ScrollView, KeyboardAvoidingView, Platform,
  TouchableWithoutFeedback, Keyboard,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useForm, Controller, Control, FieldValues, Path } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Button, Snackbar, IconButton, TextInput, HelperText } from 'react-native-paper';
import { useNavigation } from '@react-navigation/native';
import { OKO_COLORS } from '../theme';
import { useAuth } from '../context/AuthContext';
import { changePasswordSchema, ChangePasswordSchemaType } from '../utils/validators';
import api, { extractErrorMessage } from '../api/client';
import GlassCard from '../components/GlassCard';

const ChangePasswordScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const { user } = useAuth();
  const { control, handleSubmit, formState: { errors, isSubmitting }, watch, reset } = useForm<ChangePasswordSchemaType>({
    resolver: zodResolver(changePasswordSchema),
    defaultValues: { new_password: '', confirm_password: '' },
  });
  const [showNew, setShowNew] = useState(false);
  const [showConfirm, setShowConfirm] = useState(false);
  const [snack, setSnack] = useState<{ visible: boolean; msg: string; ok?: boolean }>({ visible: false, msg: '' });

  const newPwd = watch('new_password') || '';
  const strength = scorePwd(newPwd);

  const onSubmit = async (data: ChangePasswordSchemaType) => {
    try {
      if (!user?.id) throw new Error('Sesión inválida');
      await api.patch(`/usuarios/${user.id}`, { password: data.new_password });
      setSnack({ visible: true, msg: 'Contraseña actualizada correctamente', ok: true });
      setTimeout(() => {
        reset();
        navigation.goBack();
      }, 900);
    } catch (e) {
      setSnack({ visible: true, msg: extractErrorMessage(e, 'No se pudo actualizar la contraseña') });
    }
  };

  return (
    <TouchableWithoutFeedback onPress={Keyboard.dismiss}>
      <SafeAreaView style={styles.safe} edges={['bottom']}>
        <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ flex: 1 }}>
          <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false}>
            <GlassCard style={styles.glass}>
              <View style={styles.headerRow}>
                <View style={styles.iconBox}>
                  <IconButton icon="lock-reset" size={34} iconColor={OKO_COLORS.accentCyan} style={{ margin: 0 }} />
                </View>
                <View style={{ flex: 1, marginLeft: 14 }}>
                  <Text style={styles.title}>Cambiar contraseña</Text>
                  <Text style={styles.subtitle}>Protege tu cuenta con una contraseña segura.</Text>
                </View>
              </View>

              <View style={{ marginTop: 16 }}>
                <Text style={styles.label}>Nueva contraseña</Text>
                <ControllerInput
                  control={control}
                  name="new_password"
                  placeholder="Mínimo 8 caracteres con letras y números"
                  secure={!showNew}
                  rightIcon={showNew ? 'eye-off-outline' : 'eye-outline'}
                  onRight={() => setShowNew((s) => !s)}
                  error={errors.new_password?.message}
                />
                <View style={{ flexDirection: 'row', alignItems: 'center', marginTop: 8, gap: 8 }}>
                  <View style={styles.barRow}>
                    {[0, 1, 2, 3].map((i) => (
                      <View
                        key={i}
                        style={[
                          styles.bar,
                          {
                            backgroundColor:
                              i < strength.level
                                ? strength.color
                                : 'rgba(255,255,255,0.08)',
                          },
                        ]}
                      />
                    ))}
                  </View>
                  <Text style={[styles.strengthLabel, { color: strength.color }]}>{strength.label}</Text>
                </View>
              </View>

              <View style={{ marginTop: 14 }}>
                <Text style={styles.label}>Confirmar contraseña</Text>
                <ControllerInput
                  control={control}
                  name="confirm_password"
                  placeholder="Repite la nueva contraseña"
                  secure={!showConfirm}
                  rightIcon={showConfirm ? 'eye-off-outline' : 'eye-outline'}
                  onRight={() => setShowConfirm((s) => !s)}
                  error={errors.confirm_password?.message}
                />
              </View>

              <View style={{ marginTop: 18, gap: 8 }}>
                <Bullet text="Entre 8 y 64 caracteres" ok={newPwd.length >= 8 && newPwd.length <= 64} />
                <Bullet text="Sin espacios en blanco" ok={newPwd.length > 0 && !/\s/.test(newPwd)} />
                <Bullet text="Contiene letras y números" ok={/[A-Za-z]/.test(newPwd) && /\d/.test(newPwd)} />
              </View>

              <Button
                mode="contained"
                icon="content-save-check"
                loading={isSubmitting}
                disabled={isSubmitting}
                style={styles.btn}
                contentStyle={{ paddingVertical: 10 }}
                buttonColor={OKO_COLORS.accentCyan}
                textColor="#041019"
                onPress={handleSubmit(onSubmit)}
              >
                ACTUALIZAR CONTRASEÑA
              </Button>

              <Button
                mode="text"
                onPress={() => navigation.goBack()}
                textColor={OKO_COLORS.textSecondary}
                style={{ marginTop: 6 }}
              >
                Cancelar
              </Button>
            </GlassCard>

            <View style={{ height: 20 }} />
          </ScrollView>
        </KeyboardAvoidingView>

        <Snackbar
          visible={snack.visible}
          onDismiss={() => setSnack((s) => ({ ...s, visible: false }))}
          duration={3200}
          style={[styles.snack, snack.ok && { borderLeftWidth: 3, borderLeftColor: OKO_COLORS.success }]}
          action={{ label: 'OK', onPress={() => setSnack((s) => ({ ...s, visible: false })) }}
        >
          {snack.msg}
        </Snackbar>
      </SafeAreaView>
    </TouchableWithoutFeedback>
  );
};

const Bullet: React.FC<{ text: string; ok: boolean }> = ({ text, ok }) => (
  <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8 }}>
    <View style={[styles.bulletDot, { backgroundColor: ok ? OKO_COLORS.success : 'rgba(255,255,255,0.15)' }]} />
    <Text style={[styles.bulletText, { color: ok ? OKO_COLORS.textPrimary : OKO_COLORS.textSecondary }]}>{text}</Text>
  </View>
);

function ControllerInput<T extends FieldValues>({
  control, name, placeholder, secure, rightIcon, onRight, error,
}: {
  control: Control<T>;
  name: Path<T>;
  placeholder?: string;
  secure?: boolean;
  rightIcon?: string;
  onRight?: () => void;
  error?: string;
}) {
  return (
    <View>
      <Controller
        control={control}
        name={name}
        render={({ field: { value, onChange, onBlur } }) => (
          <TextInput
            mode="outlined"
            placeholder={placeholder}
            value={value as any}
            onChangeText={onChange}
            onBlur={onBlur}
            secureTextEntry={secure}
            autoCapitalize="none"
            style={{ backgroundColor: OKO_COLORS.input }}
            outlineColor={error ? OKO_COLORS.danger : OKO_COLORS.border}
            activeOutlineColor={error ? OKO_COLORS.danger : OKO_COLORS.accentCyan}
            textColor={OKO_COLORS.textPrimary}
            placeholderTextColor={OKO_COLORS.textSecondary}
            theme={{ colors: { onSurfaceVariant: OKO_COLORS.textSecondary } }}
            right={rightIcon
              ? <TextInput.Icon icon={rightIcon} color={OKO_COLORS.accentCyan} onPress={onRight} />
              : undefined}
          />
        )}
      />
      {!!error && <HelperText type="error" style={{ marginLeft: 4, color: OKO_COLORS.danger }} visible>{error}</HelperText>}
    </View>
  );
}

function scorePwd(pwd: string): { level: number; label: string; color: string } {
  let level = 0;
  if (pwd.length >= 8) level++;
  if (/[A-Za-z]/.test(pwd) && /\d/.test(pwd)) level++;
  if (/[^A-Za-z0-9]/.test(pwd)) level++;
  if (pwd.length >= 12) level++;
  if (level <= 1) return { level: 1, label: 'Débil', color: OKO_COLORS.danger };
  if (level === 2) return { level: 2, label: 'Regular', color: OKO_COLORS.warning };
  if (level === 3) return { level: 3, label: 'Buena', color: OKO_COLORS.info };
  return { level: 4, label: 'Excelente', color: OKO_COLORS.success };
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: OKO_COLORS.bgPrimary },
  scroll: { padding: 18 },
  glass: { padding: 20 },
  headerRow: { flexDirection: 'row', alignItems: 'center' },
  iconBox: {
    width: 64, height: 64, borderRadius: 18, backgroundColor: 'rgba(0,242,255,0.1)',
    alignItems: 'center', justifyContent: 'center',
  },
  title: { color: OKO_COLORS.textPrimary, fontSize: 20, fontWeight: '900' },
  subtitle: { color: OKO_COLORS.textSecondary, marginTop: 4, fontSize: 12, lineHeight: 18 },
  label: { color: OKO_COLORS.textPrimary, fontWeight: '700', marginBottom: 6, fontSize: 13 },
  barRow: { flexDirection: 'row', gap: 4, flex: 1 },
  bar: { height: 6, borderRadius: 4, flex: 1 },
  strengthLabel: { fontSize: 12, fontWeight: '800' },
  bulletDot: { width: 8, height: 8, borderRadius: 4 },
  bulletText: { fontSize: 12 },
  btn: { marginTop: 22, borderRadius: 14 },
  snack: { backgroundColor: OKO_COLORS.bgSecondary, borderWidth: 1, borderColor: OKO_COLORS.border },
});

export default ChangePasswordScreen;
