import { z } from 'zod';

export const loginSchema = z.object({
  email: z
    .string({ required_error: 'El usuario es obligatorio' })
    .min(3, 'El usuario es demasiado corto')
    .refine(
      (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) || /^[A-Za-z0-9_\-]+$/.test(v),
      'Introduce un correo o una matrícula válida'
    ),
  password: z
    .string({ required_error: 'La contraseña es obligatoria' })
    .min(8, 'La contraseña debe tener al menos 8 caracteres')
    .max(64, 'La contraseña es demasiado larga'),
});

export type LoginSchemaType = z.infer<typeof loginSchema>;

const COMUNES = new Set([
  '12345678', 'password', 'password123', 'qwerty', 'abc123',
  '11111111', '123456789', '1234567890', 'contraseña', 'contrasena',
]);

export const passwordSchema = z
  .string({ required_error: 'Campo obligatorio' })
  .min(8, 'Mínimo 8 caracteres')
  .max(64, 'Máximo 64 caracteres')
  .refine((v) => !/\s/.test(v), 'No debe contener espacios')
  .refine((v) => /[A-Za-z]/.test(v), 'Debe incluir al menos una letra')
  .refine((v) => /\d/.test(v), 'Debe incluir al menos un número')
  .refine((v) => !COMUNES.has(v.toLowerCase()), 'Contraseña demasiado común');

export const changePasswordSchema = z.object({
  new_password: passwordSchema,
  confirm_password: z.string(),
}).refine((v) => v.new_password === v.confirm_password, {
  path: ['confirm_password'],
  message: 'Las contraseñas no coinciden',
});

export type ChangePasswordSchemaType = z.infer<typeof changePasswordSchema>;

const validTipos = ['auto', 'moto', 'camioneta', 'otro'] as const;

export const vehicleSchema = z.object({
  plate: z
    .string({ required_error: 'Placa/Matrícula obligatoria' })
    .min(3, 'Mínimo 3 caracteres')
    .max(15, 'Máximo 15 caracteres')
    .regex(/^[A-Za-z0-9\-]+$/, 'Solo letras, números y guiones'),
  marca: z
    .string({ required_error: 'Marca obligatoria' })
    .min(2, 'Mínimo 2 caracteres')
    .max(50, 'Máximo 50 caracteres'),
  modelo: z
    .string({ required_error: 'Modelo obligatorio' })
    .min(1, 'Mínimo 1 caracter')
    .max(80, 'Máximo 80 caracteres'),
  anio: z
    .union([
      z.string().transform((v) => (v === '' ? null : parseInt(v, 10))),
      z.number().int().nullable(),
      z.null(),
    ])
    .refine(
      (v) => v === null || (v >= 1950 && v <= new Date().getFullYear() + 1),
      'Año fuera de rango válido (1950 - actual)'
    ),
  color: z.string().max(30, 'Máximo 30 caracteres').nullable().optional(),
  tipo: z
    .union([z.enum(validTipos), z.string().max(20), z.null()])
    .optional()
    .transform((v) => (v ? String(v).trim().toLowerCase() || null : null)),
});

export type VehicleSchemaType = z.infer<typeof vehicleSchema>;
