import React, { useState } from 'react';
import { View, StyleSheet, Text, ScrollView, Image } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Chip, Button, Divider, IconButton, TextInput, Snackbar } from 'react-native-paper';
import { useRoute, RouteProp, useNavigation } from '@react-navigation/native';
import { OKO_COLORS, severityColor } from '../theme';
import { RootStackParamList, Alert } from '../types';
import GlassCard from '../components/GlassCard';
import { fmtFecha, fmtRelative } from '../utils/formatters';
import api, { extractErrorMessage } from '../api/client';

type R = RouteProp<RootStackParamList, 'AlertDetail'>;

const AlertDetailScreen: React.FC = () => {
  const route = useRoute<R>();
  const navigation = useNavigation<any>();
  const alert: Alert = route.params.alert;
  const sevColor = severityColor(alert.severity);
  const [note, setNote] = useState(alert.notes || '');
  const [saving, setSaving] = useState(false);
  const [snack, setSnack] = useState<{ visible: boolean; msg: string; ok?: boolean }>({ visible: false, msg: '' });

  const guardarNotas = async () => {
    try {
      setSaving(true);
      await api.patch(`/alerts/${alert.id}`, { notes: note });
      setSnack({ visible: true, msg: 'Notas guardadas', ok: true });
    } catch (e) {
      setSnack({ visible: true, msg: extractErrorMessage(e, 'No se pudo guardar') });
    } finally {
      setSaving(false);
    }
  };

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>
        <GlassCard style={[styles.card, { borderTopWidth: 4, borderTopColor: sevColor }]}>
          <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' }}>
            <View style={{ flex: 1 }}>
              <View style={{ flexDirection: 'row', gap: 8, marginBottom: 8 }}>
                <Chip mode="flat" textStyle={{ color: sevColor, fontSize: 11, fontWeight: '800' }} style={{ backgroundColor: `${sevColor}20` }}>
                  {alert.severity}
                </Chip>
                {alert.status && (
                  <Chip mode="flat" textStyle={{ color: OKO_COLORS.textSecondary, fontSize: 11 }} style={{ backgroundColor: 'rgba(255,255,255,0.06)' }}>
                    {String(alert.status).toUpperCase()}
                  </Chip>
                )}
              </View>
              <Text style={styles.title}>{alert.title}</Text>
              <Text style={styles.subRelative}>{fmtRelative(alert.created_at)} · {fmtFecha(alert.created_at)}</Text>
            </View>
            <IconButton
              icon={severityIcon(alert.severity)}
              size={28}
              iconColor={sevColor}
              style={{ backgroundColor: `${sevColor}18`, margin: 0 }}
            />
          </View>

          <Divider style={{ backgroundColor: OKO_COLORS.border, marginVertical: 16 }} />

          {alert.description ? (
            <>
              <Text style={styles.section}>Descripción</Text>
              <Text style={styles.desc}>{alert.description}</Text>
            </>
          ) : null}

          <View style={{ marginTop: 16, gap: 10 }}>
            <Info label="ID alerta" value={`ALT-${String(alert.id).padStart(5, '0')}`} />
            <Info label="Vehículo" value={alert.vehicle_plate ? alert.vehicle_plate.toUpperCase() : '—'} />
            <Info label="Ubicación" value={alert.location || '—'} />
            <Info label="Propietario" value={alert.vehicle_owner ? String(alert.vehicle_owner) : '—'} />
          </View>
        </GlassCard>

        {alert.image_uri ? (
          <GlassCard style={[styles.card, { marginTop: 14 }]} padding={0}>
            <Image source={{ uri: alert.image_uri }} style={styles.image} resizeMode="cover" />
          </GlassCard>
        ) : null}

        <GlassCard style={[styles.card, { marginTop: 14 }]}>
          <Text style={styles.section}>Mis notas</Text>
          <TextInput
            mode="outlined"
            placeholder="Añade observaciones, acciones tomadas..."
            value={note}
            onChangeText={setNote}
            multiline
            numberOfLines={5}
            style={{ backgroundColor: OKO_COLORS.input, marginTop: 8 }}
            outlineColor={OKO_COLORS.border}
            activeOutlineColor={OKO_COLORS.accentCyan}
            textColor={OKO_COLORS.textPrimary}
            placeholderTextColor={OKO_COLORS.textSecondary}
            theme={{ colors: { onSurfaceVariant: OKO_COLORS.textSecondary } }}
          />
          <Button
            mode="contained"
            icon="content-save"
            loading={saving}
            disabled={saving}
            style={{ marginTop: 12, borderRadius: 14 }}
            contentStyle={{ paddingVertical: 8 }}
            buttonColor={OKO_COLORS.accentCyan}
            textColor="#041019"
            onPress={guardarNotas}
          >
            Guardar notas
          </Button>
        </GlassCard>

        <GlassCard style={[styles.card, { marginTop: 14, padding: 0 }]}>
          <View style={styles.row2}>
            <ActionBtn icon="shield-check" label="Marcar resuelto" color={OKO_COLORS.success} onPress={() => setSnack({ visible: true, msg: 'Funcionalidad próximamente', ok: true })} />
            <ActionBtn icon="share" label="Compartir" color={OKO_COLORS.accentCyan} onPress={() => setSnack({ visible: true, msg: 'Compartir alerta' })} />
          </View>
        </GlassCard>

        <Button
          mode="text"
          textColor={OKO_COLORS.textSecondary}
          style={{ marginTop: 10 }}
          onPress={() => navigation.goBack()}
        >
          Volver
        </Button>
        <View style={{ height: 20 }} />
      </ScrollView>

      <Snackbar
        visible={snack.visible}
        onDismiss={() => setSnack((s) => ({ ...s, visible: false }))}
        duration={3000}
        style={[styles.snack, snack.ok && { borderLeftWidth: 3, borderLeftColor: OKO_COLORS.success }]}
      >
        {snack.msg}
      </Snackbar>
    </SafeAreaView>
  );
};

const Info: React.FC<{ label: string; value: string }> = ({ label, value }) => (
  <View style={styles.infoRow}>
    <Text style={styles.infoLabel}>{label}</Text>
    <Text style={styles.infoValue} numberOfLines={1}>{value}</Text>
  </View>
);

const ActionBtn: React.FC<{ icon: string; label: string; color: string; onPress: () => void }> = ({ icon, label, color, onPress }) => (
  <Button
    mode="outlined"
    icon={icon}
    onPress={onPress}
    style={[styles.actionBtn, { borderColor: `${color}40` }]}
    textColor={color}
    contentStyle={{ paddingVertical: 6 }}
  >
    {label}
  </Button>
);

function severityIcon(sev: string) {
  const up = String(sev).toUpperCase();
  if (up === 'CRITICAL') return 'alert-octagon';
  if (up === 'HIGH') return 'alert-circle';
  if (up === 'MEDIUM') return 'alert';
  return 'information';
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: OKO_COLORS.bgPrimary },
  scroll: { padding: 18 },
  card: { padding: 18 },
  title: { color: OKO_COLORS.textPrimary, fontSize: 22, fontWeight: '900' },
  subRelative: { color: OKO_COLORS.textSecondary, fontSize: 12, marginTop: 4 },
  section: { color: OKO_COLORS.textPrimary, fontWeight: '800', fontSize: 14, marginBottom: 6 },
  desc: { color: OKO_COLORS.textSecondary, lineHeight: 22 },
  infoRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 4 },
  infoLabel: { color: OKO_COLORS.textSecondary, fontSize: 12, fontWeight: '600' },
  infoValue: { color: OKO_COLORS.textPrimary, fontSize: 13, fontWeight: '700', maxWidth: '60%' },
  image: { width: '100%', height: 220, borderRadius: 14 },
  row2: { flexDirection: 'row', padding: 10, gap: 8 },
  actionBtn: { flex: 1, borderRadius: 14 },
  snack: { backgroundColor: OKO_COLORS.bgSecondary, borderWidth: 1, borderColor: OKO_COLORS.border },
});

export default AlertDetailScreen;
