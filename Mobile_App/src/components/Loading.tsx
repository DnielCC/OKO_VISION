import React from 'react';
import { View, StyleSheet, ActivityIndicator, Text } from 'react-native';
import { OKO_COLORS } from '../theme';

export const Loading: React.FC<{ label?: string; full?: boolean }> = ({ label, full }) => (
  <View style={[styles.wrap, full && { flex: 1 }]}>
    <ActivityIndicator size="large" color={OKO_COLORS.accentCyan} />
    {label && <Text style={styles.txt}>{label}</Text>}
  </View>
);

const styles = StyleSheet.create({
  wrap: {
    alignItems: 'center',
    justifyContent: 'center',
    padding: 32,
    gap: 14,
  },
  txt: {
    color: OKO_COLORS.textSecondary,
    fontWeight: '600',
    marginTop: 10,
  },
});

export default Loading;
