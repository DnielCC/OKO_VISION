import React, { useEffect, useState } from 'react';
import { View, StyleSheet, Text, ScrollView, Image, Alert, KeyboardAvoidingView, Platform, TouchableWithoutFeedback, Keyboard } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Button, Snackbar, IconButton, Menu, Divider } from 'react-native-paper';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import * as ImagePicker from 'expo-image-picker';
import { OKO_COLORS } from '../theme';
import { useAuth } from '../context/AuthContext';
import { vehicleSchema, VehicleSchemaType } from '../utils/validators';
import api, { extractErrorMessage } from '../api/client';
import FormInput from '../components/FormInput';
import GlassCard from '../components/GlassCard';
import { RootStackParamList, Vehicle } from '../types';

type R = RouteProp<RootStackParamList, 'VehicleForm'>;

const TIPOS = ['auto', 'moto', 'camioneta', 'otro'];

const VehicleFormScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const route = useRoute<R>();
  const { user } = useAuth();
  const editing = !!route.params?.vehicle;
  const init: Vehicle = route.params?.vehicle || {
    plate: '', marca: '', modelo: '', anio: null, color: '', tipo: 'auto', owner_id: user?.id ?? 0,
  };

  const { control, handleSubmit, setValue, reset, formState: { errors, isSubmitting } } = useForm<VehicleSchemaType>({
    resolver: zodResolver(vehicleSchema),
    defaultValues: {
      plate: init.plate || '',
      marca: init.marca || '',
      modelo: init.modelo || '',
      anio: init.anio ?? null,
      color: init.color ?? '',
      tipo: init.tipo ?? 'auto',
    },
  });

  const [photo, setPhoto] = useState<string | null>(init.photo_uri || null);
  const [snack, setSnack] = useState<{ visible: boolean; msg: string }>({ visible: false, msg: '' });
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => {
    (async () => {
      const { status } = await ImagePicker.requestCameraPermissionsAsync();
      await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (status !== 'granted') {
        // no mostrar alertas agresivas, solo fallar silenciosamente al intentar
      }
    })();
  }, []);

  const pickFrom = async (source: 'camera' | 'gallery') => {
    try {
      const opts: ImagePicker.ImagePickerOptions = {
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: true,
        aspect: [4, 3],
        quality: 0.6,
      };
      const res = source === 'camera'
        ? await ImagePicker.launchCameraAsync(opts as any)
        : await ImagePicker.launchImageLibraryAsync(opts as any);
      if (!res.canceled && res.assets && res.assets[0]) {
        setPhoto(res.assets[0].uri);
      }
    } catch (e) {
      setSnack({ visible: true, msg: extractErrorMessage(e, 'Error al seleccionar imagen') });
    }
  };

  const onSubmit = async (data: VehicleSchemaType) => {
    try {
      const payload = {
        plate: data.plate.toUpperCase().trim(),
        marca: data.marca.trim(),
        modelo: data.modelo.trim(),
        anio: typeof data.anio === 'string' ? (data.anio === '' ? null : parseInt(data.anio, 10)) : data.anio ?? null,
        color: data.color && data.color.trim() ? data.color.trim() : null,
        tipo: data.tipo || 'otro',
        owner_id: user!.id,
        photo_uri: photo,
      };
      if (editing) {
        await api.patch(`/vehiculos/${init.id}/`, payload);
        setSnack({ visible: true, msg: 'Vehículo actualizado correctamente' });
      } else {
        await api.post('/vehiculos/', payload);
        setSnack({ visible: true, msg: 'Vehículo agregado correctamente' });
      }
      setTimeout(() => navigation.goBack(), 700);
    } catch (e) {
      setSnack({ visible: true, msg: extractErrorMessage(e, editing ? 'Error al actualizar' : 'Error al crear') });
    }
  };

  return (
    <TouchableWithoutFeedback onPress={Keyboard.dismiss}>
      <SafeAreaView style={styles.safe} edges={['bottom']}>
        <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ flex: 1 }}>
          <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false}>
            <GlassCard style={styles.glass}>
              <View style={styles.photoHeader}>
                {photo ? (
                  <Image source={{ uri: photo }} style={styles.photo} />
                ) : (
                  <View style={[styles.photo, styles.photoEmpty]}>
                    <IconButton icon="car-outline" size={64} iconColor={OKO_COLORS.accentCyan} style={{ margin: 0 }} />
                    <Text style={styles.photoEmptyText}>Sin foto</Text>
                  </View>
                )}
                <View style={styles.photoBtns}>
                  <Menu
                    visible={menuOpen}
                    onDismiss={() => setMenuOpen(false)}
                    anchor={
                      <Button mode="contained-tonal" icon="camera-plus" onPress={() => setMenuOpen(true)} buttonColor="rgba(0,242,255,0.12)" textColor={OKO_COLORS.accentCyan}>
                        Agregar foto
                      </Button>
                    }
                    contentStyle={{ backgroundColor: OKO_COLORS.bgSecondary }}
                  >
                    <Menu.Item leadingIcon="camera" onPress={() => { setMenuOpen(false); pickFrom('camera'); }} title="Tomar foto" titleStyle={{ color: OKO_COLORS.textPrimary }} />
                    <Divider style={{ backgroundColor: OKO_COLORS.border }} />
                    <Menu.Item leadingIcon="image" onPress={() => { setMenuOpen(false); pickFrom('gallery'); }} title="Desde galería" titleStyle={{ color: OKO_COLORS.textPrimary }} />
                  </Menu>
                  {photo && (
                    <Button mode="text" icon="trash-can-outline" textColor={OKO_COLORS.danger} onPress={() => setPhoto(null)}>
                      Quitar
                    </Button>
                  )}
                </View>
              </View>

              <Text style={styles.section}>Datos del vehículo</Text>

              <FormInput
                control={control}
                name="plate"
                label="Placa / Matrícula"
                error={errors.plate?.message}
                autoCapitalize="characters"
                left={<IconButton icon="tag-outline" size={18} iconColor={OKO_COLORS.accentCyan} style={{ margin: 0 }} />}
              />
              <View style={styles.row2}>
                <View style={{ flex: 1, marginRight: 6 }}>
                  <FormInput
                    control={control}
                    name="marca"
                    label="Marca"
                    error={errors.marca?.message}
                    autoCapitalize="words"
                  />
                </View>
                <View style={{ flex: 1, marginLeft: 6 }}>
                  <FormInput
                    control={control}
                    name="modelo"
                    label="Modelo"
                    error={errors.modelo?.message}
                    autoCapitalize="words"
                  />
                </View>
              </View>
              <View style={styles.row2}>
                <View style={{ flex: 1, marginRight: 6 }}>
                  <FormInput
                    control={control}
                    name="anio"
                    label="Año"
                    error={(errors.anio as any)?.message}
                    keyboardType="numeric"
                    maxLength={4}
                  />
                </View>
                <View style={{ flex: 1, marginLeft: 6 }}>
                  <FormInput
                    control={control}
                    name="color"
                    label="Color"
                    error={(errors.color as any)?.message}
                    autoCapitalize="words"
                  />
                </View>
              </View>

              <Text style={styles.labelTipo}>Tipo de vehículo</Text>
              <View style={styles.chipWrap}>
                {TIPOS.map((t) => (
                  <ChipLike
                    key={t}
                    label={t.charAt(0).toUpperCase() + t.slice(1)}
                    onPress={() => setValue('tipo', t as any, { shouldDirty: true })}
                    active={String(control._formValues.tipo || 'auto').toLowerCase() === t.toLowerCase()}
                  />
                ))}
              </View>
              {(errors.tipo as any)?.message && <Text style={styles.errTxt}>{(errors.tipo as any).message}</Text>}

              <Button
                mode="contained"
                icon={editing ? 'content-save' : 'plus-circle'}
                loading={isSubmitting}
                disabled={isSubmitting}
                style={styles.btn}
                contentStyle={{ paddingVertical: 10 }}
                buttonColor={OKO_COLORS.accentCyan}
                textColor="#041019"
                onPress={handleSubmit(onSubmit)}
              >
                {editing ? 'GUARDAR CAMBIOS' : 'REGISTRAR VEHÍCULO'}
              </Button>
            </GlassCard>
            <View style={{ height: 30 }} />
          </ScrollView>
        </KeyboardAvoidingView>

        <Snackbar
          visible={snack.visible}
          onDismiss={() => setSnack((s) => ({ ...s, visible: false }))}
          duration={3000}
          style={styles.snack}
        >
          {snack.msg}
        </Snackbar>
      </SafeAreaView>
    </TouchableWithoutFeedback>
  );
};

const ChipLike: React.FC<{ label: string; active?: boolean; onPress: () => void }> = ({ label, active, onPress }) => (
  <Button
    mode={active ? 'contained' : 'outlined'}
    onPress={onPress}
    style={[styles.chipBtn, active && { backgroundColor: OKO_COLORS.accentCyan, borderColor: OKO_COLORS.accentCyan }]}
    textColor={active ? '#041019' : OKO_COLORS.textPrimary}
    contentStyle={{ height: 34 }}
  >
    {label}
  </Button>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: OKO_COLORS.bgPrimary },
  scroll: { padding: 18 },
  glass: { padding: 20 },
  photoHeader: { alignItems: 'center', marginBottom: 18, gap: 12 },
  photo: { width: 140, height: 140, borderRadius: 24, borderWidth: 1, borderColor: OKO_COLORS.border },
  photoEmpty: { alignItems: 'center', justifyContent: 'center', borderStyle: 'dashed', backgroundColor: 'rgba(0,242,255,0.05)' },
  photoEmptyText: { color: OKO_COLORS.textSecondary, marginTop: -6, fontSize: 12 },
  photoBtns: { flexDirection: 'row', gap: 10, alignItems: 'center' },
  section: { color: OKO_COLORS.textPrimary, fontSize: 16, fontWeight: '800', marginBottom: 12 },
  row2: { flexDirection: 'row' },
  labelTipo: { color: OKO_COLORS.textPrimary, fontWeight: '700', marginTop: 4, marginBottom: 8 },
  chipWrap: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginBottom: 8 },
  chipBtn: { borderRadius: 20, marginVertical: 2 },
  btn: { marginTop: 20, borderRadius: 14 },
  errTxt: { color: OKO_COLORS.danger, fontSize: 12, marginLeft: 8, marginTop: -2 },
  snack: { backgroundColor: OKO_COLORS.bgSecondary, borderWidth: 1, borderColor: OKO_COLORS.border },
});

export default VehicleFormScreen;
