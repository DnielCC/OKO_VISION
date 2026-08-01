import React, { useCallback, useEffect, useRef, useState } from 'react';
import { View, StyleSheet, Text, ScrollView, Share, Alert as RNAlert } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button, Snackbar, IconButton, Divider, Chip, ActivityIndicator } from 'react-native-paper';
import { useNavigation } from '@react-navigation/native';
import * as FileSystem from 'expo-file-system';
import * as Sharing from 'expo-sharing';
import ViewShot, { captureRef } from 'react-native-view-shot';
import QRCode from 'react-native-qrcode-svg';
import { useAuth } from '../context/AuthContext';
import { OKO_COLORS } from '../theme';
import api, { extractErrorMessage } from '../api/client';
import GlassCard from '../components/GlassCard';
import { fmtNombre } from '../utils/formatters';

type QrPayload = {
  t?: string;
  uid?: number;
  uname?: string;
  email?: string;
  v?: number;
  ts?: number;
  sig?: string;
  plate?: string;
  [k: string]: unknown;
};

const QRScreen: React.FC = () => {
  const { user } = useAuth();
  const navigation = useNavigation<any>();
  const viewShotRef = useRef<any>(null);
  const [snack, setSnack] = useState<{ visible: boolean; msg: string }>({ visible: false, msg: '' });
  const [loading, setLoading] = useState(true);
  const [payload, setPayload] = useState<QrPayload | null>(null);
  const [refreshing, setRefreshing] = useState(false);

  const generarPayloadQR = useCallback(async () => {
    setLoading(true);
    try {
      const r = await api.get<QrPayload>('/qr/payload');
      if (r.data && typeof r.data === 'object') {
        setPayload(r.data);
      } else {
        throw new Error('Respuesta inválida');
      }
    } catch (e) {
      // Fallback: generar payload local si el servidor no responde
      const fallback: QrPayload = {
        t: 'okovision_user',
        uid: user?.id,
        uname: (user?.nombre ? `${user.nombre} ${user.apellidos || ''}`.trim() : '') || user?.username || '',
        email: user?.email || '',
        v: 1,
        ts: Math.floor(Date.now() / 1000),
      };
      setPayload(fallback);
      setSnack({ visible: true, msg: extractErrorMessage(e, 'Usando QR local') });
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [user?.id, user?.username, user?.nombre, user?.apellidos, user?.email]);

  useEffect(() => {
    if (user) generarPayloadQR();
  }, [user, generarPayloadQR]);

  const regenerarQR = async () => {
    setRefreshing(true);
    await generarPayloadQR();
  };

  const qrString = payload ? JSON.stringify(payload) : '';

  const downloadImage = async () => {
    if (!qrString) {
      setSnack({ visible: true, msg: 'Aún no hay QR para exportar' });
      return;
    }
    try {
      const uri: string = await captureRef(viewShotRef, {
        format: 'png',
        quality: 1,
        result: 'data-uri',
      } as any);
      const fileUri = FileSystem.documentDirectory + `QR_${user?.username || 'user'}_${Date.now()}.png`;
      const clean = uri.replace(/^data:image\/\w+;base64,/, '');
      await FileSystem.writeAsStringAsync(fileUri, clean, { encoding: FileSystem.EncodingType.Base64 });
      const canShare = await Sharing.isAvailableAsync();
      if (canShare) {
        await Sharing.shareAsync(fileUri, {
          mimeType: 'image/png',
          dialogTitle: 'Compartir QR OKO VISION',
          UTI: 'public.png',
        });
      } else {
        setSnack({ visible: true, msg: `QR guardado en: ${fileUri}` });
      }
    } catch (e: any) {
      setSnack({ visible: true, msg: e?.message || 'Error al exportar QR' });
    }
  };

  const sharePayload = async () => {
    try {
      await Share.share({
        title: 'QR acceso OKO VISION',
        message:
          `Mi QR de acceso a OKO VISION:\n` +
          `Usuario: ${user?.username}\n` +
          `Correo: ${user?.email}\n` +
          (payload?.sig ? `Firma: ${payload.sig.slice(0, 8)}...\n` : '') +
          `Generado: ${new Date((payload?.ts || Date.now() / 1000) * 1000).toLocaleString()}`,
      });
    } catch (e: any) {
      setSnack({ visible: true, msg: e?.message || 'No se pudo compartir' });
    }
  };

  const fechaQR = payload?.ts ? new Date(payload.ts * 1000).toLocaleString() : '-';

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>
        <View style={styles.header}>
          <View>
            <Text style={styles.title}>Mi QR de acceso</Text>
            <Text style={styles.subtitle}>
              Muestra este código en los lectores de acceso o compártelo.
            </Text>
          </View>
          <Button
            mode="text"
            icon="refresh"
            textColor={OKO_COLORS.accentCyan}
            onPress={regenerarQR}
            loading={refreshing}
            disabled={refreshing || loading}
            style={{ alignSelf: 'flex-start' }}
          >
            Regenerar
          </Button>
        </View>

        {loading ? (
          <GlassCard style={{ padding: 40 }}>
            <ActivityIndicator animating size="large" color={OKO_COLORS.accentCyan} />
            <Text style={{ color: OKO_COLORS.textSecondary, textAlign: 'center', marginTop: 12 }}>
              Generando código QR firmado...
            </Text>
          </GlassCard>
        ) : (
          <ViewShot ref={viewShotRef} options={{ format: 'png', quality: 1 }}>
            <GlassCard neon style={styles.qrCard}>
              <View style={styles.qrHeader}>
                <View style={styles.qrLogo}>
                  <IconButton
                    icon="eye-outline"
                    size={32}
                    iconColor={OKO_COLORS.accentCyan}
                    style={{ margin: 0 }}
                  />
                </View>
                <View>
                  <Text style={styles.qrBrand}>OKO VISION</Text>
                  <Text style={styles.qrBrandSub}>Acceso inteligente</Text>
                </View>
                {payload?.sig ? (
                  <Chip
                    mode="flat"
                    icon="shield-check"
                    textStyle={{ color: OKO_COLORS.success, fontSize: 10 }}
                    style={{ marginLeft: 'auto', backgroundColor: 'rgba(0,255,135,0.08)' }}
                  >
                    Firmado
                  </Chip>
                ) : null}
              </View>
              <Divider style={{ backgroundColor: OKO_COLORS.border, marginVertical: 16 }} />
              <View style={styles.qrWrap}>
                <View style={styles.qrBox}>
                  <QRCode
                    value={qrString}
                    size={220}
                    color="#050A18"
                    backgroundColor={OKO_COLORS.textPrimary}
                    logoSize={32}
                    logoBackgroundColor={OKO_COLORS.textPrimary}
                    ecl="H"
                  />
                </View>
              </View>
              <Divider style={{ backgroundColor: OKO_COLORS.border, marginVertical: 16 }} />
              <View style={styles.qrInfo}>
                <InfoLine label="Nombre" value={fmtNombre(user)} />
                <InfoLine label="Matrícula" value={user?.username || '-'} />
                <InfoLine label="Correo" value={user?.email || '-'} />
                <InfoLine label="ID" value={`USR-${String(user?.id).padStart(4, '0')}`} />
                <InfoLine label="Generado" value={fechaQR} />
              </View>
              <View style={styles.validity}>
                <Chip
                  mode="flat"
                  icon="clock-check"
                  textStyle={{ color: OKO_COLORS.success, fontSize: 11 }}
                  style={{ backgroundColor: 'rgba(0,255,135,0.08)' }}
                >
                  {payload?.sig ? 'Válido · Firmado servidor' : 'Válido indefinidamente'}
                </Chip>
              </View>
            </GlassCard>
          </ViewShot>
        )}

        <View style={styles.actions}>
          <Button
            mode="contained"
            icon="share-variant"
            onPress={downloadImage}
            style={[styles.btn, { flex: 1 }]}
            buttonColor={OKO_COLORS.accentCyan}
            textColor="#041019"
            contentStyle={{ paddingVertical: 8 }}
            disabled={loading}
          >
            Compartir imagen
          </Button>
          <Button
            mode="outlined"
            icon="message-text-outline"
            onPress={sharePayload}
            style={[styles.btn, { flex: 1 }]}
            textColor={OKO_COLORS.accentCyan}
            contentStyle={{ paddingVertical: 8 }}
            disabled={loading}
          >
            Compartir texto
          </Button>
        </View>

        <GlassCard style={styles.tipCard} padding={16}>
          <View style={{ flexDirection: 'row', alignItems: 'flex-start', gap: 10 }}>
            <IconButton
              icon="lightbulb-on-outline"
              size={22}
              iconColor={OKO_COLORS.warning}
              style={{ margin: 0, backgroundColor: 'rgba(255,165,0,0.08)' }}
            />
            <View style={{ flex: 1 }}>
              <Text style={styles.tipTitle}>Consejos de uso</Text>
              <Text style={styles.tipText}>
                · Guarda el QR en tu galería para usarlo sin internet.{'\n'}
                · Puedes escanear códigos QR desde el botón inferior.{'\n'}
                · Si el QR no es reconocido, presiona "Regenerar".
              </Text>
            </View>
          </View>
        </GlassCard>

        <Button
          mode="elevated"
          icon="qrcode-scan"
          onPress={() => navigation.navigate('QRScanner')}
          style={styles.scanBtn}
          textColor={OKO_COLORS.accentCyan}
          buttonColor={OKO_COLORS.bgSecondary}
        >
          Abrir escáner de QR
        </Button>
        <View style={{ height: 40 }} />
      </ScrollView>

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

const InfoLine: React.FC<{ label: string; value: string }> = ({ label, value }) => (
  <View style={styles.infoLine}>
    <Text style={styles.infoLabel}>{label}</Text>
    <Text style={styles.infoValue} numberOfLines={1}>
      {value}
    </Text>
  </View>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: OKO_COLORS.bgPrimary },
  scroll: { paddingHorizontal: 18, paddingTop: 10 },
  header: {
    paddingHorizontal: 4,
    marginBottom: 18,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  title: { color: OKO_COLORS.textPrimary, fontSize: 24, fontWeight: '900' },
  subtitle: { color: OKO_COLORS.textSecondary, marginTop: 4, lineHeight: 20, maxWidth: '70%' },
  qrCard: { padding: 22 },
  qrHeader: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  qrLogo: {
    width: 54,
    height: 54,
    borderRadius: 16,
    backgroundColor: 'rgba(0,242,255,0.12)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  qrBrand: {
    color: OKO_COLORS.accentCyan,
    fontSize: 22,
    fontWeight: '900',
    letterSpacing: 1,
  },
  qrBrandSub: { color: OKO_COLORS.textSecondary, fontSize: 12, marginTop: -2 },
  qrWrap: { alignItems: 'center', paddingVertical: 6 },
  qrBox: {
    padding: 16,
    backgroundColor: OKO_COLORS.textPrimary,
    borderRadius: 22,
    shadowColor: OKO_COLORS.accentCyan,
    shadowOpacity: 0.25,
    shadowRadius: 18,
  },
  qrInfo: { gap: 10 },
  infoLine: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  infoLabel: { color: OKO_COLORS.textSecondary, fontWeight: '600', fontSize: 13 },
  infoValue: {
    color: OKO_COLORS.textPrimary,
    fontWeight: '800',
    fontSize: 13,
    maxWidth: '65%',
  },
  validity: { marginTop: 14, alignItems: 'center' },
  actions: { flexDirection: 'row', gap: 10, marginTop: 18 },
  btn: { borderRadius: 14 },
  tipCard: { marginTop: 18 },
  tipTitle: { color: OKO_COLORS.textPrimary, fontWeight: '800', marginBottom: 4 },
  tipText: { color: OKO_COLORS.textSecondary, fontSize: 12, lineHeight: 18 },
  scanBtn: {
    marginTop: 20,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: OKO_COLORS.border,
  },
  snack: {
    backgroundColor: OKO_COLORS.bgSecondary,
    borderWidth: 1,
    borderColor: OKO_COLORS.border,
  },
});

export default QRScreen;
