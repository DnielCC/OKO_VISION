import React, { useEffect, useRef, useState } from 'react';
import { View, StyleSheet, Text, TouchableOpacity, Vibration } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button, Dialog, Portal, Snackbar, IconButton, Chip, ActivityIndicator } from 'react-native-paper';
import { useNavigation } from '@react-navigation/native';
import { CameraView, useCameraPermissions } from 'expo-camera';
import { OKO_COLORS } from '../theme';
import api, { extractErrorMessage } from '../api/client';
import { AccessLog } from '../types';
import { useAuth } from '../context/AuthContext';

type QrPayload = {
  t?: string;
  uid?: number;
  uname?: string;
  email?: string;
  v?: number;
  ts?: number;
  plate?: string;
  sig?: string;
  [k: string]: unknown;
};

const QRScannerScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const { user } = useAuth();
  const [permission, requestPermission] = useCameraPermissions();
  const [scanned, setScanned] = useState(false);
  const [payload, setPayload] = useState<QrPayload | null>(null);
  const [rawText, setRawText] = useState<string>('');
  const [processing, setProcessing] = useState(false);
  const [result, setResult] = useState<{ ok: boolean; msg: string; access?: AccessLog } | null>(null);
  const [snack, setSnack] = useState<{ visible: boolean; msg: string }>({ visible: false, msg: '' });
  const qrLockRef = useRef(false);

  const resetScan = () => {
    qrLockRef.current = false;
    setScanned(false);
    setPayload(null);
    setRawText('');
    setResult(null);
  };

  const parsePayload = (data: string): QrPayload | null => {
    try {
      if (data.trim().startsWith('{')) return JSON.parse(data) as QrPayload;
    } catch {}
    try {
      const decoded = decodeURIComponent(data);
      if (decoded.startsWith('{')) return JSON.parse(decoded) as QrPayload;
    } catch {}
    return { raw: data } as any;
  };

  const handleBarCodeScanned = async ({ data }: { data: string; type: string }) => {
    if (qrLockRef.current) return;
    qrLockRef.current = true;
    setScanned(true);
    setRawText(data);
    Vibration.vibrate(50);

    const parsed = parsePayload(data);
    setPayload(parsed);

    if (parsed && parsed.t === 'okovision_user') {
      await registrarAcceso(parsed);
    } else {
      setResult({ ok: false, msg: 'Código QR no reconocido por OKO VISION.' });
    }
  };

  const registrarAcceso = async (parsed: QrPayload) => {
    try {
      setProcessing(true);
      const body: Partial<AccessLog> = {
        user_id: parsed.uid ?? user?.id ?? 0,
        type: 'entry',
        method: 'qr',
        notes: `QR OKO VISION · escaneado por ${user?.username}`,
        timestamp: new Date().toISOString(),
      };
      const r = await api.post<AccessLog>('/accesos/', body);
      setResult({
        ok: true,
        msg: `¡Acceso registrado correctamente para ${parsed.uname || parsed.uid}!`,
        access: r.data,
      });
      Vibration.vibrate([0, 60, 60, 60]);
    } catch (e) {
      setResult({ ok: false, msg: extractErrorMessage(e, 'No se pudo registrar el acceso.') });
    } finally {
      setProcessing(false);
    }
  };

  if (!permission) {
    return (
      <SafeAreaView style={styles.safe}>
        <View style={styles.center}>
          <ActivityIndicator animating size="large" color={OKO_COLORS.accentCyan} />
          <Text style={{ color: OKO_COLORS.textSecondary, marginTop: 12 }}>Solicitando permisos de cámara...</Text>
        </View>
      </SafeAreaView>
    );
  }

  if (!permission.granted) {
    return (
      <SafeAreaView style={styles.safe}>
        <View style={styles.center}>
          <IconButton icon="camera-off" size={64} iconColor={OKO_COLORS.warning} style={{ backgroundColor: 'rgba(255,165,0,0.1)' }} />
          <Text style={styles.title}>Permisos requeridos</Text>
          <Text style={styles.subtitle}>Necesitamos acceso a la cámara para escanear códigos QR.</Text>
          <Button
            mode="contained"
            icon="camera"
            buttonColor={OKO_COLORS.accentCyan}
            textColor="#041019"
            style={{ marginTop: 18, borderRadius: 14 }}
            onPress={() => requestPermission()}
          >
            Conceder permiso
          </Button>
          <Button mode="text" textColor={OKO_COLORS.textSecondary} onPress={() => navigation.goBack()} style={{ marginTop: 8 }}>
            Volver
          </Button>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} hitSlop={12}>
          <IconButton icon="arrow-left" iconColor={OKO_COLORS.textPrimary} style={{ margin: 0 }} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Escanear QR</Text>
        <View style={{ width: 48 }} />
      </View>

      <View style={{ flex: 1 }}>
        <CameraView
          style={StyleSheet.absoluteFillObject}
          facing="back"
          onBarcodeScanned={scanned ? undefined : handleBarCodeScanned}
          barcodeScannerSettings={{ barcodeTypes: ['qr'] }}
        />
        <View style={styles.overlay}>
          <View style={styles.maskOuter}>
            <View style={[styles.row, { flex: 1 }]}>
              <View style={styles.maskBlock} />
              <View style={styles.maskTopBottom} />
              <View style={styles.maskBlock} />
            </View>
            <View style={[styles.row, { height: 280 }]}>
              <View style={styles.maskBlock} />
              <View style={styles.scanBox}>
                <View style={[styles.corner, styles.tl]} />
                <View style={[styles.corner, styles.tr]} />
                <View style={[styles.corner, styles.bl]} />
                <View style={[styles.corner, styles.br]} />
                <View style={[styles.scanLine, scanned && { opacity: 0 }]} />
              </View>
              <View style={styles.maskBlock} />
            </View>
            <View style={[styles.row, { flex: 1 }]}>
              <View style={styles.maskBlock} />
              <View style={styles.maskTopBottom} />
              <View style={styles.maskBlock} />
            </View>
          </View>
        </View>
      </View>

      <View style={styles.bottomPanel}>
        <Text style={styles.hint}>Coloca el QR dentro del recuadro</Text>
        {scanned && !result && (
          <Button
            mode="contained"
            loading={processing}
            disabled={processing}
            style={{ marginTop: 12, borderRadius: 14 }}
            buttonColor={OKO_COLORS.accentCyan}
            textColor="#041019"
            onPress={resetScan}
          >
            {processing ? 'Procesando...' : 'Volver a escanear'}
          </Button>
        )}
      </View>

      <Portal>
        <Dialog visible={!!result} onDismiss={() => {}} dismissable={false}>
          <Dialog.Icon
            icon={result?.ok ? 'check-decagram' : 'alert-circle'}
            color={result?.ok ? OKO_COLORS.success : OKO_COLORS.danger}
            size={56}
          />
          <Dialog.Title style={{ textAlign: 'center', color: OKO_COLORS.textPrimary }}>
            {result?.ok ? 'QR válido' : 'Escaneo fallido'}
          </Dialog.Title>
          <Dialog.Content>
            <Text style={{ color: OKO_COLORS.textSecondary, textAlign: 'center' }}>
              {result?.msg}
            </Text>
            {payload?.t === 'okovision_user' && (
              <View style={{ marginTop: 14, gap: 6 }}>
                <Row label="Usuario" value={payload.uname || '-'} />
                <Row label="Correo" value={payload.email || '-'} />
                <Row label="ID" value={`#${payload.uid ?? '-'}`} />
              </View>
            )}
          </Dialog.Content>
          <Dialog.Actions style={{ justifyContent: 'center', paddingBottom: 16 }}>
            <Button
              mode="contained"
              icon="camera-retake"
              onPress={resetScan}
              buttonColor={OKO_COLORS.accentCyan}
              textColor="#041019"
              style={{ borderRadius: 14 }}
            >
              Escanear otro
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

const Row: React.FC<{ label: string; value: string }> = ({ label, value }) => (
  <View style={{ flexDirection: 'row', justifyContent: 'space-between' }}>
    <Text style={{ color: OKO_COLORS.textSecondary, fontSize: 12 }}>{label}</Text>
    <Text style={{ color: OKO_COLORS.textPrimary, fontSize: 12, fontWeight: '700' }} numberOfLines={1}>{value}</Text>
  </View>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#000' },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: 24 },
  title: { color: OKO_COLORS.textPrimary, fontSize: 20, fontWeight: '800', marginTop: 12 },
  subtitle: { color: OKO_COLORS.textSecondary, textAlign: 'center', marginTop: 8, lineHeight: 20 },
  header: {
    backgroundColor: 'rgba(0,0,0,0.6)',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 4,
    paddingVertical: 4,
    zIndex: 20,
  },
  headerTitle: { color: OKO_COLORS.textPrimary, fontWeight: '800', fontSize: 16 },
  overlay: { ...StyleSheet.absoluteFillObject },
  maskOuter: { flex: 1 },
  row: { flexDirection: 'row' },
  maskBlock: { flex: 1, backgroundColor: 'rgba(0,0,0,0.6)' },
  maskTopBottom: { flex: 2, backgroundColor: 'rgba(0,0,0,0.6)' },
  scanBox: { flex: 2, overflow: 'visible' },
  corner: { position: 'absolute', width: 28, height: 28, borderColor: OKO_COLORS.accentCyan, borderWidth: 4 },
  tl: { top: 0, left: 0, borderRightWidth: 0, borderBottomWidth: 0 },
  tr: { top: 0, right: 0, borderLeftWidth: 0, borderBottomWidth: 0 },
  bl: { bottom: 0, left: 0, borderRightWidth: 0, borderTopWidth: 0 },
  br: { bottom: 0, right: 0, borderLeftWidth: 0, borderTopWidth: 0 },
  scanLine: {
    position: 'absolute', left: 8, right: 8, top: '50%', height: 2, backgroundColor: OKO_COLORS.accentCyan,
    shadowColor: OKO_COLORS.accentCyan, shadowOpacity: 0.9, shadowRadius: 8,
  },
  bottomPanel: {
    backgroundColor: 'rgba(0,0,0,0.75)',
    paddingHorizontal: 20,
    paddingVertical: 18,
    alignItems: 'center',
  },
  hint: { color: OKO_COLORS.textPrimary, fontWeight: '700' },
  snack: { backgroundColor: OKO_COLORS.bgSecondary, borderWidth: 1, borderColor: OKO_COLORS.border },
});

export default QRScannerScreen;
