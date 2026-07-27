import React from 'react';
import { View, StyleSheet, Text } from 'react-native';
import { IconButton } from 'react-native-paper';
import { OKO_COLORS } from '../theme';
import GlassCard from './GlassCard';

interface Props {
  icon?: string;
  title: string;
  message?: string;
  action?: React.ReactNode;
}

export const EmptyState: React.FC<Props> = ({
  icon = 'inbox-outline', title, message, action,
}) => (
  <GlassCard style={styles.card}>
    <View style={styles.center}>
      <View style={styles.iconWrap}>
        <IconButton icon={icon} size={48} iconColor={OKO_COLORS.accentCyan} />
      </View>
      <Text style={styles.title}>{title}</Text>
      {message && <Text style={styles.msg}>{message}</Text>}
      {action && <View style={styles.act}>{action}</View>}
    </View>
  </GlassCard>
);

const styles = StyleSheet.create({
  card: { marginHorizontal: 8, marginTop: 16 },
  center: { alignItems: 'center', paddingVertical: 12, gap: 10 },
  iconWrap: {
    width: 92, height: 92, borderRadius: 46,
    backgroundColor: 'rgba(0,242,255,0.1)',
    alignItems: 'center', justifyContent: 'center',
    marginBottom: 6,
  },
  title: {
    color: OKO_COLORS.textPrimary,
    fontSize: 17, fontWeight: '700',
  },
  msg: {
    color: OKO_COLORS.textSecondary,
    textAlign: 'center', lineHeight: 20,
    paddingHorizontal: 16,
  },
  act: { marginTop: 10 },
});

export default EmptyState;
