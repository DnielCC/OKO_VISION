import React from 'react';
import { View, StyleSheet, Text } from 'react-native';
import { IconButton } from 'react-native-paper';
import { OKO_COLORS } from '../theme';
import GlassCard from './GlassCard';

interface Props {
  title: string;
  value: string | number;
  icon: string;
  color?: string;
  subtitle?: string;
  onPress?: () => void;
}

export const KpiCard: React.FC<Props> = ({
  title, value, icon, color, subtitle, onPress,
}) => (
  <GlassCard neon={!!onPress} style={styles.card} padding={14} onTouchEnd={onPress}>
    <View style={styles.row}>
      <View style={[styles.iconWrap, { backgroundColor: (color || OKO_COLORS.accentCyan) + '22' }]}>
        <IconButton icon={icon} size={24} iconColor={color || OKO_COLORS.accentCyan} style={{ margin: 0 }} />
      </View>
      <View style={{ flex: 1 }}>
        <Text style={styles.title}>{title}</Text>
        <Text style={[styles.value, { color: color || OKO_COLORS.accentCyan }]}>{value}</Text>
        {subtitle && <Text style={styles.sub}>{subtitle}</Text>}
      </View>
    </View>
  </GlassCard>
);

const styles = StyleSheet.create({
  card: { flex: 1, minWidth: '45%', margin: 4 },
  row: { flexDirection: 'row', alignItems: 'center', gap: 10 },
  iconWrap: {
    width: 46, height: 46, borderRadius: 14,
    alignItems: 'center', justifyContent: 'center',
  },
  title: { color: OKO_COLORS.textSecondary, fontSize: 11, fontWeight: '600', textTransform: 'uppercase', letterSpacing: 0.4 },
  value: { fontSize: 26, fontWeight: '900', marginTop: 2 },
  sub: { color: OKO_COLORS.textSecondary, fontSize: 11, marginTop: 2 },
});

export default KpiCard;
