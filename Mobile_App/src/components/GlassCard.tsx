import React from 'react';
import { View, StyleSheet, ViewProps } from 'react-native';
import { OKO_COLORS } from '../theme';

interface GlassCardProps extends ViewProps {
  neon?: boolean;
  padding?: number;
}

export const GlassCard: React.FC<GlassCardProps> = ({
  style, neon, padding, children, ...rest
}) => (
  <View
    style={[
      styles.card,
      neon && styles.neon,
      { padding: padding ?? 18 },
      style,
    ]}
    {...rest}
  >
    {children}
  </View>
);

const styles = StyleSheet.create({
  card: {
    backgroundColor: OKO_COLORS.glass,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: OKO_COLORS.border,
  },
  neon: {
    borderColor: OKO_COLORS.accentCyan,
    shadowColor: OKO_COLORS.accentCyan,
    shadowOpacity: 0.18,
    shadowRadius: 14,
    shadowOffset: { width: 0, height: 0 },
    elevation: 3,
  },
});

export default GlassCard;
