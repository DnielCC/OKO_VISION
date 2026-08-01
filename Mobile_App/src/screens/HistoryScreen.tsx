import React, { useCallback, useEffect, useState } from 'react';
import { View, StyleSheet, Text, ScrollView, RefreshControl, TouchableOpacity } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { IconButton, Chip, Searchbar, SegmentedButtons, Snackbar } from 'react-native-paper';
import { useAuth } from '../context/AuthContext';
import { OKO_COLORS } from '../theme';
import api, { extractErrorMessage } from '../api/client';
import GlassCard from '../components/GlassCard';
import Loading from '../components/Loading';
import EmptyState from '../components/EmptyState';
import { fmtFecha, fmtRelative, fmtTipoAcceso } from '../utils/formatters';
import { AccessLog } from '../types';

type FilterType = 'all' | 'entry' | 'exit';

const HistoryScreen: React.FC = () => {
  const { user } = useAuth();
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [allAccess, setAllAccess] = useState<AccessLog[]>([]);
  const [filter, setFilter] = useState<FilterType>('all');
  const [search, setSearch] = useState('');
  const [snack, setSnack] = useState<{ visible: boolean; msg: string }>({ visible: false, msg: '' });

  const load = useCallback(async (ref = false) => {
    try {
      if (ref) setRefreshing(true); else setLoading(true);
      const { data } = await api.get<AccessLog[]>('/accesos/');
      const myId = user?.id ?? -1;
      const mine = (Array.isArray(data) ? data : [])
        .filter((a) => a.user_id === myId)
        .sort((a, b) => new Date(b.timestamp || 0).getTime() - new Date(a.timestamp || 0).getTime());
      setAllAccess(mine);
    } catch (e) {
      setSnack({ visible: true, msg: extractErrorMessage(e, 'Error al cargar historial') });
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [user?.id]);

  useEffect(() => {
    if (user) load();
  }, [user, load]);

  const filtered = allAccess.filter((a) => {
    if (filter !== 'all' && a.type !== filter) return false;
    if (search.trim()) {
      const q = search.trim().toLowerCase();
      const plate = String(a.vehicle_plate || '').toLowerCase();
      const method = String(a.method || '').toLowerCase();
      const notes = String(a.notes || '').toLowerCase();
      if (!plate.includes(q) && !method.includes(q) && !notes.includes(q)) return false;
    }
    return true;
  });

  const entryTotal = allAccess.filter((a) => a.type === 'entry').length;
  const exitTotal = allAccess.filter((a) => a.type === 'exit').length;

  if (loading) return <SafeAreaView style={styles.safe}><Loading full label="Cargando historial..." /></SafeAreaView>;

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <View style={styles.header}>
        <View>
          <Text style={styles.title}>Historial</Text>
          <Text style={styles.subtitle}>{allAccess.length} registros · {entryTotal} entradas · {exitTotal} salidas</Text>
        </View>
      </View>

      <View style={{ paddingHorizontal: 18 }}>
        <Searchbar
          placeholder="Buscar por placa, método..."
          value={search}
          onChangeText={setSearch}
          style={styles.search}
          iconColor={OKO_COLORS.accentCyan}
          placeholderTextColor={OKO_COLORS.textSecondary}
          inputStyle={{ color: OKO_COLORS.textPrimary }}
          theme={{ colors: { onSurfaceVariant: OKO_COLORS.textSecondary } }}
        />
        <SegmentedButtons
          value={filter}
          onValueChange={(v) => setFilter(v as FilterType)}
          buttons={[
            { value: 'all', label: 'Todos', icon: 'format-list-bulleted' },
            { value: 'entry', label: 'Entradas', icon: 'arrow-down' },
            { value: 'exit', label: 'Salidas', icon: 'arrow-up' },
          ]}
          style={styles.seg}
          theme={{ colors: { secondaryContainer: 'rgba(0,242,255,0.15)', onSecondaryContainer: OKO_COLORS.accentCyan, outline: OKO_COLORS.border } }}
        />
      </View>

      <ScrollView
        contentContainerStyle={styles.scroll}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl tintColor={OKO_COLORS.accentCyan} colors={[OKO_COLORS.accentCyan]} refreshing={refreshing} onRefresh={() => load(true)} />}
      >
        {filtered.length === 0 ? (
          <EmptyState
            icon="history"
            title="Sin registros"
            message={search || filter !== 'all' ? 'No hay resultados para el filtro actual.' : 'Tu historial de accesos aparecerá aquí.'}
          />
        ) : (
          filtered.map((a, i) => {
            const t = fmtTipoAcceso(a.type);
            return (
              <GlassCard key={a.id || i} style={[styles.card, { borderLeftColor: t.color, borderLeftWidth: 3 }]} padding={0}>
                <View style={styles.row}>
                  <View style={[styles.dot, { backgroundColor: t.color }]} />
                  <View style={{ flex: 1 }}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between' }}>
                      <Text style={styles.titleRow}>
                        {t.label} {a.vehicle_plate ? <Text style={{ color: OKO_COLORS.accentCyan }}> · {a.vehicle_plate.toUpperCase()}</Text> : null}
                      </Text>
                      <Text style={styles.time}>{fmtRelative(a.timestamp)}</Text>
                    </View>
                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 4, flexWrap: 'wrap' }}>
                      {a.method && (
                        <Chip mode="flat" textStyle={{ fontSize: 10 }} style={{ backgroundColor: 'rgba(0,242,255,0.08)' }}>
                          {String(a.method).toUpperCase()}
                        </Chip>
                      )}
                      <Text style={styles.sub}>{fmtFecha(a.timestamp)}</Text>
                    </View>
                    {a.notes ? <Text style={styles.notes} numberOfLines={2}>📝 {a.notes}</Text> : null}
                  </View>
                  <IconButton icon={t.icon} size={20} iconColor={t.color} style={{ margin: 0 }} />
                </View>
              </GlassCard>
            );
          })
        )}
        <View style={{ height: 40 }} />
      </ScrollView>

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

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: OKO_COLORS.bgPrimary },
  header: { paddingHorizontal: 22, paddingVertical: 14 },
  title: { color: OKO_COLORS.textPrimary, fontSize: 24, fontWeight: '900' },
  subtitle: { color: OKO_COLORS.textSecondary, marginTop: 2, fontSize: 12 },
  search: { marginBottom: 10, backgroundColor: OKO_COLORS.bgSecondary, borderRadius: 14 },
  seg: { marginBottom: 14 },
  scroll: { paddingHorizontal: 18, paddingTop: 4, gap: 10 },
  card: { padding: 0 },
  row: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 16, paddingVertical: 14, gap: 12 },
  dot: { width: 10, height: 10, borderRadius: 5 },
  titleRow: { color: OKO_COLORS.textPrimary, fontWeight: '700', fontSize: 14 },
  time: { color: OKO_COLORS.textSecondary, fontSize: 11 },
  sub: { color: OKO_COLORS.textSecondary, fontSize: 12 },
  notes: { color: OKO_COLORS.textSecondary, fontSize: 12, marginTop: 6, lineHeight: 18 },
  snack: { backgroundColor: OKO_COLORS.bgSecondary, borderWidth: 1, borderColor: OKO_COLORS.border, bottom: 80 },
});

export default HistoryScreen;
