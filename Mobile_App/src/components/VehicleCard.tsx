import React from 'react';
import { View, StyleSheet, Text, Image, TouchableOpacity } from 'react-native';
import { IconButton, Chip } from 'react-native-paper';
import { OKO_COLORS } from '../theme';
import GlassCard from './GlassCard';
import { Vehicle } from '../types';
import { fmtTipoVehiculo } from '../utils/formatters';

interface Props {
  vehicle: Vehicle;
  onEdit?: (v: Vehicle) => void;
  onDelete?: (v: Vehicle) => void;
}

export const VehicleCard: React.FC<Props> = ({ vehicle, onEdit, onDelete }) => (
  <GlassCard style={styles.card}>
    <View style={styles.row}>
      {vehicle.photo_uri ? (
        <Image source={{ uri: vehicle.photo_uri }} style={styles.photo} />
      ) : (
        <View style={[styles.photo, styles.photoPlaceholder]}>
          <IconButton icon="car" size={40} iconColor={OKO_COLORS.accentCyan} style={{ margin: 0 }} />
        </View>
      )}
      <View style={{ flex: 1, marginLeft: 14 }}>
        <View style={styles.rowTop}>
          <Text style={styles.plate} numberOfLines={1}>{vehicle.plate.toUpperCase()}</Text>
          <Chip mode="flat" style={styles.typeChip} textStyle={{ color: OKO_COLORS.accentCyan, fontSize: 11 }}>
            {fmtTipoVehiculo(vehicle.tipo)}
          </Chip>
        </View>
        <Text style={styles.model} numberOfLines={1}>
          {vehicle.marca} {vehicle.modelo}{vehicle.anio ? ` · ${vehicle.anio}` : ''}
        </Text>
        {vehicle.color && <Text style={styles.color}>Color: {vehicle.color}</Text>}
        <View style={styles.actions}>
          <TouchableOpacity style={styles.btn} onPress={() => onEdit?.(vehicle)} activeOpacity={0.7}>
            <IconButton icon="pencil-outline" size={18} iconColor={OKO_COLORS.accentCyan} style={{ margin: 0 }} />
            <Text style={styles.btnTxt}>Editar</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.btn} onPress={() => onDelete?.(vehicle)} activeOpacity={0.7}>
            <IconButton icon="delete-outline" size={18} iconColor={OKO_COLORS.danger} style={{ margin: 0 }} />
            <Text style={[styles.btnTxt, { color: OKO_COLORS.danger }]}>Eliminar</Text>
          </TouchableOpacity>
        </View>
      </View>
    </View>
  </GlassCard>
);

const styles = StyleSheet.create({
  card: { marginBottom: 12 },
  row: { flexDirection: 'row', alignItems: 'flex-start' },
  photo: { width: 92, height: 92, borderRadius: 16, backgroundColor: OKO_COLORS.card },
  photoPlaceholder: { alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: OKO_COLORS.border, borderStyle: 'dashed' },
  rowTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  plate: { color: OKO_COLORS.accentCyan, fontWeight: '900', fontSize: 22, letterSpacing: 1 },
  typeChip: { backgroundColor: 'rgba(0,242,255,0.1)', height: 26 },
  model: { color: OKO_COLORS.textPrimary, fontSize: 14, fontWeight: '700', marginTop: 2 },
  color: { color: OKO_COLORS.textSecondary, fontSize: 12, marginTop: 4 },
  actions: { flexDirection: 'row', marginTop: 10, gap: 8 },
  btn: {
    flexDirection: 'row', alignItems: 'center',
    paddingHorizontal: 10, paddingVertical: 6,
    borderRadius: 10, backgroundColor: OKO_COLORS.input,
  },
  btnTxt: { color: OKO_COLORS.textPrimary, fontWeight: '700', fontSize: 12, marginLeft: -4 },
});

export default VehicleCard;
