import React, { useCallback, useEffect, useState } from 'react';
import {
  View, StyleSheet, Text, ScrollView, RefreshControl, TouchableOpacity,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { IconButton, Avatar, Chip, Snackbar, FAB } from 'react-native-paper';
import { useNavigation } from '@react-navigation/native';
import { useAuth } from '../context/AuthContext';
import { OKO_COLORS } from '../theme';
import api, { extractErrorMessage } from '../api/client';
import GlassCard from '../components/GlassCard';
import KpiCard from '../components/KpiCard';
import Loading from '../components/Loading';
import EmptyState from '../components/EmptyState';
import { fmtFecha, fmtNombre, fmtRelative, fmtTipoAcceso } from '../utils/formatters';
import { AccessLog, Alert, Vehicle } from '../types';

const HomeScreen: React.FC = () => {
  const { user } = useAuth();
  const navigation = useNavigation<any>();
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [vehicles, setVehicles] = useState<Vehicle[]>([]);
  const [recentAccess, setRecentAccess] = useState<AccessLog[]>([]);
  const [myAlerts, setMyAlerts] = useState<Alert[]>([]);
  const [snack, setSnack] = useState<{ visible: boolean; msg: string }>({ visible: false, msg: '' });

  const load = useCallback(async () => {
    try {
      const [vehiclesR, accessR, alertsR] = await Promise.allSettled([
        api.get<Vehicle[]>('/vehiculos/'),
        api.get<AccessLog[]>('/accesos/'),
        api.get<Alert[]>('/alerts/'),
      ]);
      const myId = user?.id ?? -1;
      let vs: Vehicle[] = [];
      let acs: AccessLog[] = [];
      let alts: Alert[] = [];

      if (vehiclesR.status === 'fulfilled') {
        const raw = Array.isArray(vehiclesR.value.data) ? vehiclesR.value.data : [];
        vs = raw.filter((v) => v.owner_id === myId);
      }
      if (accessR.status === 'fulfilled') {
        const raw = Array.isArray(accessR.value.data) ? accessR.value.data : [];
        acs = raw.filter((a) => a.user_id === myId).sort((a, b) =>
          new Date(b.timestamp || 0).getTime() - new Date(a.timestamp || 0).getTime()
        ).slice(0, 6);
      }
      if (alertsR.status === 'fulfilled') {
        const raw = Array.isArray(alertsR.value.data) ? alertsR.value.data : [];
        const myPlates = new Set(vs.map((v) => v.plate));
        alts = raw.filter((a) =>
          a.vehicle_owner === myId || (a.vehicle_plate && myPlates.has(a.vehicle_plate))
        ).sort((a, b) =>
          new Date(b.created_at || 0).getTime() - new Date(a.created_at || 0).getTime()
        ).slice(0, 3);
      }
      setVehicles(vs);
      setRecentAccess(acs);
      setMyAlerts(alts);
    } catch (e) {
      setSnack({ visible: true, msg: extractErrorMessage(e, 'Error al cargar datos') });
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [user?.id]);

  useEffect(() => {
    if (user) load();
  }, [user, load]);

  const onRefresh = () => {
    setRefreshing(true);
    load();
  };

  const entryCount = recentAccess.filter((a) => a.type === 'entry').length;
  const exitCount = recentAccess.filter((a) => a.type === 'exit').length;
  const alertsCritical = myAlerts.filter((a) => a.severity === 'CRITICAL' || a.severity === 'HIGH').length;

  if (loading) return <SafeAreaView style={styles.safe}><Loading full label="Cargando panel..." /></SafeAreaView>;

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <View style={styles.header}>
        <View style={{ flex: 1 }}>
          <Text style={styles.greet}>¡Hola, 👋</Text>
          <Text style={styles.userName} numberOfLines={1}>{fmtNombre(user)}</Text>
        </View>
        <Avatar.Text
          size={48}
          label={fmtNombre(user).slice(0, 2).toUpperCase()}
          color="#041019"
          style={{ backgroundColor: OKO_COLORS.accentCyan }}
        />
      </View>

      <ScrollView
        contentContainerStyle={styles.scroll}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl tintColor={OKO_COLORS.accentCyan} colors={[OKO_COLORS.accentCyan]} refreshing={refreshing} onRefresh={onRefresh} />}
      >
        <View style={styles.row}>
          <KpiCard title="Mis vehículos" value={vehicles.length} icon="car" color={OKO_COLORS.accentCyan} onPress={() => navigation.navigate('Vehicles')} />
          <KpiCard title="Alertas activas" value={myAlerts.length} icon="alert-circle" color={alertsCritical > 0 ? OKO_COLORS.danger : OKO_COLORS.warning} subtitle={`${alertsCritical} graves`} />
        </View>
        <View style={styles.row}>
          <KpiCard title="Entradas" value={entryCount} icon="arrow-down" color={OKO_COLORS.success} subtitle="Últimos accesos" />
          <KpiCard title="Salidas" value={exitCount} icon="arrow-up" color={OKO_COLORS.warning} subtitle="Últimos accesos" />
        </View>

        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Acciones rápidas</Text>
        </View>
        <View style={styles.quickRow}>
          {[
            { icon: 'qrcode-plus', label: 'Mostrar QR', screen: 'QR' },
            { icon: 'qrcode-scan', label: 'Escanear', screen: 'QRScanner' },
            { icon: 'plus-circle', label: '+ Vehículo', screen: 'VehicleForm' },
            { icon: 'history', label: 'Historial', screen: 'History' },
          ].map((act) => (
            <TouchableOpacity
              key={act.label}
              style={styles.quickBtn}
              onPress={() => act.screen === 'QRScanner' || act.screen === 'VehicleForm' || act.screen === 'ChangePassword'
                ? navigation.navigate(act.screen as any)
                : navigation.navigate(act.screen)}
              activeOpacity={0.7}
            >
              <View style={styles.quickIcon}>
                <IconButton icon={act.icon} size={26} iconColor={OKO_COLORS.accentCyan} style={{ margin: 0 }} />
              </View>
              <Text style={styles.quickLabel}>{act.label}</Text>
            </TouchableOpacity>
          ))}
        </View>

        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Últimos accesos</Text>
          <Chip mode="flat" textStyle={{ color: OKO_COLORS.accentCyan, fontSize: 11 }} onPress={() => navigation.navigate('History')}>
            Ver todos
          </Chip>
        </View>
        {recentAccess.length === 0 ? (
          <EmptyState title="Sin accesos registrados" message="Todavía no tienes entradas o salidas del campus." />
        ) : (
          <GlassCard style={{ marginTop: 8 }} padding={0}>
            {recentAccess.map((a, i) => {
              const t = fmtTipoAcceso(a.type);
              return (
                <View key={a.id || i} style={[styles.accessRow, i !== recentAccess.length - 1 && styles.accessDiv]}>
                  <View style={[styles.dot, { backgroundColor: t.color }]} />
                  <View style={{ flex: 1 }}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between' }}>
                      <Text style={styles.accessTitle}>
                        {t.label} {a.vehicle_plate ? `· ${a.vehicle_plate}` : ''}
                      </Text>
                      <Text style={styles.accessTime}>{fmtRelative(a.timestamp)}</Text>
                    </View>
                    <Text style={styles.accessSub}>
                      {a.method ? `${String(a.method).toUpperCase()} · ` : ''}{fmtFecha(a.timestamp)}
                    </Text>
                  </View>
                </View>
              );
            })}
          </GlassCard>
        )}

        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Alertas recientes</Text>
        </View>
        {myAlerts.length === 0 ? (
          <EmptyState icon="shield-check" title="Sin alertas" message="¡Excelente! No hay alertas para tus vehículos." />
        ) : (
          <View style={{ gap: 10 }}>
            {myAlerts.map((a) => (
              <TouchableOpacity key={a.id} onPress={() => navigation.navigate('AlertDetail', { alert: a })} activeOpacity={0.8}>
                <GlassCard style={{ borderLeftColor: severityColor(a.severity), borderLeftWidth: 3 }}>
                  <View style={{ flexDirection: 'row', justifyContent: 'space-between' }}>
                    <Text style={styles.alertTitle} numberOfLines={1}>{a.title}</Text>
                    <Chip mode="flat" textStyle={{ fontSize: 10, color: severityColor(a.severity) }} style={{ paddingHorizontal: 0 }}>
                      {a.severity}
                    </Chip>
                  </View>
                  {a.description && <Text style={styles.alertDesc} numberOfLines={2}>{a.description}</Text>}
                  <Text style={styles.alertSub}>{fmtRelative(a.created_at)} {a.vehicle_plate ? `· ${a.vehicle_plate}` : ''}</Text>
                </GlassCard>
              </TouchableOpacity>
            ))}
          </View>
        )}

        <View style={{ height: 80 }} />
      </ScrollView>

      <FAB
        icon="qrcode-plus"
        label="Mostrar QR"
        style={styles.fab}
        color="#041019"
        onPress={() => navigation.navigate('QR')}
      />

      <Snackbar
        visible={snack.visible}
        onDismiss={() => setSnack((s) => ({ ...s, visible: false }))}
        duration={3200}
        style={styles.snack}
      >
        {snack.msg}
      </Snackbar>
    </SafeAreaView>
  );
};

const severityColor = (s: string) => {
  const up = String(s).toUpperCase();
  if (up === 'CRITICAL') return OKO_COLORS.critical;
  if (up === 'HIGH') return OKO_COLORS.high;
  if (up === 'MEDIUM') return OKO_COLORS.medium;
  return OKO_COLORS.low;
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: OKO_COLORS.bgPrimary },
  header: {
    paddingHorizontal: 22, paddingTop: 10, paddingBottom: 12,
    flexDirection: 'row', alignItems: 'center', gap: 12,
  },
  greet: { color: OKO_COLORS.textSecondary, fontSize: 13, fontWeight: '600' },
  userName: { color: OKO_COLORS.textPrimary, fontSize: 22, fontWeight: '800', marginTop: 2 },
  scroll: { paddingHorizontal: 18, paddingTop: 4 },
  row: { flexDirection: 'row', gap: 8, marginBottom: 8 },
  sectionHeader: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    marginTop: 18, marginBottom: 6, paddingHorizontal: 2,
  },
  sectionTitle: { color: OKO_COLORS.textPrimary, fontSize: 16, fontWeight: '800' },
  quickRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 8,
    gap: 6,
  },
  quickBtn: { alignItems: 'center', gap: 4, flex: 1 },
  quickIcon: {
    width: 56, height: 56, borderRadius: 18,
    backgroundColor: OKO_COLORS.glass,
    borderWidth: 1, borderColor: OKO_COLORS.border,
    alignItems: 'center', justifyContent: 'center',
  },
  quickLabel: { color: OKO_COLORS.textPrimary, fontSize: 11, fontWeight: '600', textAlign: 'center' },
  accessRow: {
    flexDirection: 'row', alignItems: 'center', paddingHorizontal: 18, paddingVertical: 14, gap: 12,
  },
  accessDiv: { borderBottomWidth: 1, borderBottomColor: OKO_COLORS.border },
  dot: { width: 10, height: 10, borderRadius: 5 },
  accessTitle: { color: OKO_COLORS.textPrimary, fontWeight: '700', fontSize: 14 },
  accessTime: { color: OKO_COLORS.textSecondary, fontSize: 11 },
  accessSub: { color: OKO_COLORS.textSecondary, fontSize: 12, marginTop: 2 },
  alertTitle: { color: OKO_COLORS.textPrimary, fontSize: 14, fontWeight: '700', flex: 1, marginRight: 8 },
  alertDesc: { color: OKO_COLORS.textSecondary, fontSize: 12, marginTop: 6, lineHeight: 18 },
  alertSub: { color: OKO_COLORS.textSecondary, fontSize: 11, marginTop: 8 },
  fab: {
    position: 'absolute', right: 18, bottom: 24,
    backgroundColor: OKO_COLORS.accentCyan,
    borderRadius: 30,
  },
  snack: { backgroundColor: OKO_COLORS.bgSecondary, borderWidth: 1, borderColor: OKO_COLORS.border, bottom: 80 },
});

export default HomeScreen;
