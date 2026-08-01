import React from 'react';
import { Control, Controller, FieldValues, Path } from 'react-hook-form';
import { Platform, StyleSheet, View, Text, TextInputProps } from 'react-native';
import { TextInput, HelperText } from 'react-native-paper';
import { OKO_COLORS } from '../theme';

interface Props<T extends FieldValues> extends Omit<TextInputProps, 'onChange' | 'value'> {
  control: Control<T>;
  name: Path<T>;
  label: string;
  error?: string;
  rules?: any;
  containerStyle?: any;
  left?: React.ReactNode;
  right?: React.ReactNode;
}

export function FormInput<T extends FieldValues>({
  control, name, label, error, rules, containerStyle, style, left, right, secureTextEntry, editable = true, ...rest
}: Props<T>) {
  return (
    <View style={[styles.field, containerStyle]} pointerEvents="box-none">
      <Controller
        control={control}
        name={name}
        rules={rules}
        render={({ field: { value, onChange, onBlur } }) => (
          <View pointerEvents="auto" style={{ width: '100%' }}>
            <TextInput
              {...(rest as any)}
              label={label}
              mode="outlined"
              editable={editable}
              value={value != null ? String(value) : ''}
              onChangeText={(t) => onChange(t)}
              onBlur={onBlur}
              left={left as any}
              right={right as any}
              secureTextEntry={secureTextEntry}
              style={[
                { backgroundColor: OKO_COLORS.input, minHeight: Platform.OS === 'web' ? 56 : undefined },
                style,
              ]}
              outlineColor={error ? OKO_COLORS.danger : OKO_COLORS.border}
              activeOutlineColor={error ? OKO_COLORS.danger : OKO_COLORS.accentCyan}
              textColor={OKO_COLORS.textPrimary}
              placeholderTextColor={OKO_COLORS.textSecondary}
              autoCapitalize={rest.autoCapitalize ?? 'none'}
              autoCorrect={false}
              spellCheck={false}
              theme={{
                colors: {
                  onSurfaceVariant: OKO_COLORS.textSecondary,
                },
              } as any}
            />
          </View>
        )}
      />
      {!!error && <HelperText type="error" style={styles.err} visible>{error}</HelperText>}
    </View>
  );
}

const styles = StyleSheet.create({
  field: { marginBottom: 6, width: '100%' },
  err: { marginTop: -2, marginLeft: 4, color: OKO_COLORS.danger },
});

export default FormInput;
