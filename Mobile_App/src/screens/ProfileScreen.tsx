import React, { useState } from 'react';
import { View, StyleSheet, Text, ScrollView, TouchableOpacity, Share } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Avatar, Button, List, Divider, Switch, Snackbar, Dialog, Portal } from 'react-native-paper';
import { useNavigation } from '@react-navigation/native';
import { useAuth } from '../context/AuthContext';
import { OKO_COLORS } from '../theme';
import GlassCard from '../components/GlassCard';
import { fmtNombre } from '../utils/formatters';

const ProfileScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const { user, logout, biometryEnabled, setBiometryEnabled } = useAuth();
  const [snack, setSnack] = useState<{ visible: boolean; msg: string }>({ visible: false, msg: '' });
  const [confirmLogout, setConfirmLogout] = useState(false);

  const fullName = fmtNombre(user);
  const initials = fullName.slice(0, 2).toUpperCase();

  const shareProfile = async () => {
    try {
      await Share.share({
        title: 'Mi perfil OKO VISION',
        message: `Perfil de OKO VISION:\n${fullName}\nMatrícula: ${user?.username}\nCorreo: ${user?.email}`,
      });
    } catch (e: any) {
      setSnack({ visible: true, msg: e?.message || 'No se pudo compartir' });
    }
  };

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>
        <GlassCard neon style={styles.profileCard}>
          <View style={styles.avatarRow}>
            <Avatar.Text
              size={78}
              label={initials}
              color="#041019"
              style={{ backgroundColor: OKO_COLORS.accentCyan }}
            />
            <View style={{ flex: 1, marginLeft: 16 }}>
              <Text style={styles.name} numberOfLines={1}>{fullName}</Text>
              <Text style={styles.matricula}>🎓 {user?.username || '-'} </Text>
              <Text style={styles.email} numberOfLines={1}>📧 {user?.email || '-'}</Text>
              <View style={{ flexDirection: 'row', gap: 6, marginTop: 8 }}>
                {user?.activo ? (
                  <ChipCompact text="Cuenta activa" color={OKO_COLORS.success} bg="rgba(0,255,135,0.1)" />
                ) : (
                  <ChipCompact text="Pendiente" color={OKO_COLORS.warning} bg="rgba(255,165,0,0.1)" />
                )}
                <ChipCompact text={`ID #${user?.id ?? 0}`} color={OKO_COLORS.accentCyan} bg="rgba(0,242,255,0.08)" />
              </View>
            </View>
          </View>
        </GlassCard>

        <GlassCard style={{ marginTop: 14 }} padding={0}>
          <Text style={styles.section}>Seguridad</Text>
          <TouchableOpacity onPress={() => navigation.navigate('ChangePassword')} activeOpacity={0.7}>
            <List.Item
              title="Cambiar contraseña"
              description="Actualiza tu acceso"
              left={(p) => <List.Icon {...p} icon="lock-reset" color={OKO_COLORS.accentCyan} />}
              right={(p) => <List.Icon {...p} icon="chevron-right" color={OKO_COLORS.textSecondary} />}
              titleStyle={styles.listTitle}
              descriptionStyle={styles.listDesc}
              style={styles.listItem}
            />
          </TouchableOpacity>
          <Divider style={{ backgroundColor: OKO_COLORS.border }} />
          <List.Item
            title="Acceso biométrico"
            description={biometryEnabled ? 'Activado para esta sesión' : 'Usar huella o Face ID'}
            left={(p) => <List.Icon {...p} icon="fingerprint" color={OKO_COLORS.accentCyan} />}
            right={() => (
              <Switch
                value={biometryEnabled}
                onValueChange={(v) => {
                  setBiometryEnabled(v);
                  setSnack({ visible: true, msg: v ? 'Biometría activada' : 'Biometría desactivada' });
                }}
                color={OKO_COLORS.accentCyan}
              />
            )}
            titleStyle={styles.listTitle}
            descriptionStyle={styles.listDesc}
            style={styles.listItem}
          />
        </GlassCard>

        <GlassCard style={{ marginTop: 14 }} padding={0}>
          <Text style={styles.section}>Atajos</Text>
          <TouchableOpacity onPress={() => navigation.navigate('QR')} activeOpacity={0.7}>
            <List.Item
              title="Mi QR de acceso"
              description="Mostrar, compartir o guardar"
              left={(p) => <List.Icon {...p} icon="qrcode" color={OKO_COLORS.accentCyan} />}
              right={(p) => <List.Icon {...p} icon="chevron-right" color={OKO_COLORS.textSecondary} />}
              titleStyle={styles.listTitle}
              descriptionStyle={styles.listDesc}
              style={styles.listItem}
            />
          </TouchableOpacity>
          <Divider style={{ backgroundColor: OKO_COLORS.border }} />
          <TouchableOpacity onPress={() => navigation.navigate('Vehicles')} activeOpacity={0.7}>
            <List.Item
              title="Mis vehículos"
              description="Gestiona tus coches, motos..."
              left={(p) => <List.Icon {...p} icon="car-multiple" color={OKO_COLORS.accentCyan} />}
              right={(p) => <List.Icon {...p} icon="chevron-right" color={OKO_COLORS.textSecondary} />}
              titleStyle={styles.listTitle}
              descriptionStyle={styles.listDesc}
              style={styles.listItem}
            />
          </TouchableOpacity>
          <Divider style={{ backgroundColor: OKO_COLORS.border }} />
          <TouchableOpacity onPress={() => navigation.navigate('History')} activeOpacity={0.7}>
            <List.Item
              title="Historial de accesos"
              description="Entradas y salidas del campus"
              left={(p) => <List.Icon {...p} icon="clipboard-text-clock" color={OKO_COLORS.accentCyan} />}
              right={(p) => <List.Icon {...p} icon="chevron-right" color={OKO_COLORS.textSecondary} />}
              titleStyle={styles.listTitle}
              descriptionStyle={styles.listDesc}
              style={styles.listItem}
            />
          </TouchableOpacity>
        </GlassCard>

        <GlassCard style={{ marginTop: 14 }} padding={0}>
          <Text style={styles.section}>Perfil</Text>
          <TouchableOpacity onPress={shareProfile} activeOpacity={0.7}>
            <List.Item
              title="Compartir perfil"
              description="Envía tu info por WhatsApp, correo..."
              left={(p) => <List.Icon {...p} icon="share-variant" color={OKO_COLORS.accentCyan} />}
              right={(p) => <List.Icon {...p} icon="chevron-right" color={OKO_COLORS.textSecondary} />}
              titleStyle={styles.listTitle}
              descriptionStyle={styles.listDesc}
              style={styles.listItem}
            />
          </TouchableOpacity>
        </GlassCard>

        <Button
          mode="outlined"
          icon="logout"
          textColor={OKO_COLORS.danger}
          style={styles.logoutBtn}
          onPress={() => setConfirmLogout(true)}
        >
          Cerrar sesión
        </Button>

        <View style={{ height: 30 }} />
      </ScrollView>

      <Portal>
        <Dialog visible={confirmLogout} onDismiss={() => setConfirmLogout(false)}>
          <Dialog.Title style={{ color: OKO_COLORS.textPrimary }}>Cerrar sesión</Dialog.Title>
          <Dialog.Content>
            <Text style={{ color: OKO_COLORS.textSecondary }}>
              ¿Estás seguro que deseas salir de OKO VISION?
            </Text>
          </Dialog.Content>
          <Dialog.Actions>
            <Button onPress={() => setConfirmLogout(false)} textColor={OKO_COLORS.textSecondary}>Cancelar</Button>
            <Button
              onPress={async () => {
                setConfirmLogout(false);
                await logout();
              }}
              textColor={OKO_COLORS.danger}
            >
              Salir
            </Button>
          </Dialog.Actions>
        </Dialog>
      </Portal>

      <Snackbar
        visible={snack.visible}
        onDismiss={() => setSnack((s) => ({ ...s, visible: false }))}
        duration={3000}
        style={styles.snack}
      >
        {snack.msg}
      </Snackbar>
    </SafeAreaView>
  );
};

const ChipCompact: React.FC<{ text: string; color: string; bg: string }> = ({ text, color, bg }) => (
  <View style={[styles.chip, { backgroundColor: bg }]}>
    <Text style={[styles.chipText, { color }]}>{text}</Text>
  </View>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: OKO_COLORS.bgPrimary },
  scroll: { paddingHorizontal: 18, paddingTop: 14 },
  profileCard: { padding: 20 },
  avatarRow: { flexDirection: 'row', alignItems: 'center' },
  name: { color: OKO_COLORS.textPrimary, fontSize: 20, fontWeight: '900' },
  matricula: { color: OKO_COLORS.textSecondary, marginTop: 4, fontSize: 13, fontWeight: '600' },
  email: { color: OKO_COLORS.textSecondary, marginTop: 2, fontSize: 12 },
  section: { color: OKO_COLORS.textPrimary, fontWeight: '800', fontSize: 14, paddingHorizontal: 16, paddingTop: 14, paddingBottom: 6 },
  listTitle: { color: OKO_COLORS.textPrimary, fontWeight: '700', fontSize: 14 },
  listDesc: { color: OKO_COLORS.textSecondary, fontSize: 12 },
  listItem: { paddingVertical: 6 },
  chip: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
  chipText: { fontSize: 11, fontWeight: '700' },
  logoutBtn: { marginTop: 22, borderRadius: 14, borderColor: 'rgba(255,49,49,0.3)', paddingVertical: 4 },
  snack: { backgroundColor: OKO_COLORS.bgSecondary, borderWidth: 1, borderColor: OKO_COLORS.border, bottom: 80 },
});

export default ProfileScreen;
