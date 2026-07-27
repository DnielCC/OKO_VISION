import React, { useCallback, useEffect, useState } from 'react';
import { View, StyleSheet, Text, ScrollView, RefreshControl, Alert, TouchableOpacity } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { FAB, Snackbar, Dialog, Portal, Button, IconButton } from 'react-native-paper';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import { useAuth } from '../context/AuthContext';
import { OKO_COLORS } from '../theme';
import api, { extractErrorMessage } from '../api/client';
import GlassCard from '../components/GlassCard';
import Loading from '../components/Loading';
import EmptyState from '../components/EmptyState';
import VehicleCard from '../components/VehicleCard';
import { Vehicle } from '../types';

const VehiclesScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const { user } = useAuth();
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [list, setList] = useState<Vehicle[]>([]);
  const [snack, setSnack] = useState<{ visible: boolean; msg: string }>({ visible: false, msg: '' });
  const [deleteConfirm, setDeleteConfirm] = useState<Vehicle | null>(null);
  const [deleting, setDeleting] = useState(false);

  const load = useCallback(async (ref = false) => {
    try {
      if (ref) setRefreshing(true); else setLoading(true);
      const { data } = await api.get<Vehicle[]>('/vehiculos/');
      const myId = user?.id ?? -1;
      const mine = (Array.isArray(data) ? data : []).filter((v) => v.owner_id === myId);
      setList(mine);
    } catch (e) {
      setSnack({ visible: true, msg: extractErrorMessage(e, 'Error al cargar vehículos') });
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [user?.id]);

  useEffect(() => {
    if (user) load();
  }, [user, load]);

  useFocusEffect(
    useCallback(() => {
      if (user) load(true);
    }, [user, load])
  );

  const confirmDelete = (v: Vehicle) => setDeleteConfirm(v);

  const doDelete = async () => {
    if (!deleteConfirm) return;
    try {
      setDeleting(true);
      await api.delete(`/vehiculos/${deleteConfirm.id}/`);
      setSnack({ visible: true, msg: 'Vehículo eliminado correctamente' });
      setList((curr) => curr.filter((v) => v.id !== deleteConfirm.id));
    } catch (e) {
      setSnack({ visible: true, msg: extractErrorMessage(e, 'Error al eliminar') });
    } finally {
      setDeleting(false);
      setDeleteConfirm(null);
    }
  };

  if (loading) return <SafeAreaView style={styles.safe}><Loading full label="Cargando vehículos..." /></SafeAreaView>;

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <View style={styles.header}>
        <View>
          <Text style={styles.title}>Mis vehículos</Text>
          <Text style={styles.subtitle}>{list.length} registrados</Text>
        </View>
      </View>

      <ScrollView
        contentContainerStyle={styles.scroll}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl tintColor={OKO_COLORS.accentCyan} colors={[OKO_COLORS.accentCyan]} refreshing={refreshing} onRefresh={() => load(true)} />}
      >
        {list.length === 0 ? (
          <EmptyState
            icon="car"
            title="No tienes vehículos"
            message="Registra tu auto o moto para que el sistema OKO VISION lo reconozca al ingresar."
            action={
              <Button
                mode="contained"
                icon="plus"
                buttonColor={OKO_COLORS.accentCyan}
                textColor="#041019"
                onPress={() => navigation.navigate('VehicleForm')}
              >
                Agregar vehículo
              </Button>
            }
          />
        ) : (
          list.map((v) => (
            <VehicleCard
              key={v.id || v.plate}
              vehicle={v}
              onEdit={(veh) => navigation.navigate('VehicleForm', { vehicle: veh })}
              onDelete={confirmDelete}
            />
          ))
        )}
        <View style={{ height: 90 }} />
      </ScrollView>

      <FAB
        icon="plus"
        label="Agregar vehículo"
        style={styles.fab}
        color="#041019"
        onPress={() => navigation.navigate('VehicleForm')}
      />

      <Portal>
        <Dialog visible={!!deleteConfirm} onDismiss={() => !deleting && setDeleteConfirm(null)}>
          <Dialog.Title style={{ color: OKO_COLORS.textPrimary }}>Eliminar vehículo</Dialog.Title>
          <Dialog.Content>
            <Text style={{ color: OKO_COLORS.textSecondary }}>
              ¿Confirmas que deseas eliminar <Text style={{ color: OKO_COLORS.danger, fontWeight: '800' }}>{deleteConfirm?.plate.toUpperCase()}</Text>?
              Esta acción no se puede deshacer.
            </Text>
          </Dialog.Content>
          <Dialog.Actions>
            <Button onPress={() => setDeleteConfirm(null)} disabled={deleting} textColor={OKO_COLORS.textSecondary}>Cancelar</Button>
            <Button onPress={doDelete} loading={deleting} disabled={deleting} textColor={OKO_COLORS.danger}>Eliminar</Button>
          </Dialog.Actions>
        </Dialog>
      </Portal>

      <Snackbar
        visible={snack.visible}
        onDismiss={() => setSnack((s) => ({ ...s, visible: false }))}
        duration={3000}
        style={styles.snack}
        action={{ label: 'OK', onPress: () => setSnack((s) => ({ ...s, visible: false })) }}
      >
        {snack.msg}
      </Snackbar>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: OKO_COLORS.bgPrimary },
  header: { paddingHorizontal: 22, paddingVertical: 14 },
  title: { color: OKO_COLORS.textPrimary, fontSize: 24, fontWeight: '900' },
  subtitle: { color: OKO_COLORS.textSecondary, marginTop: 2 },
  scroll: { paddingHorizontal: 18, paddingTop: 4 },
  fab: { position: 'absolute', right: 18, bottom: 24, backgroundColor: OKO_COLORS.accentCyan, borderRadius: 30 },
  snack: { backgroundColor: OKO_COLORS.bgSecondary, borderWidth: 1, borderColor: OKO_COLORS.border, bottom: 80 },
});

export default VehiclesScreen;
