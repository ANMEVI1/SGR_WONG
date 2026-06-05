# 📅 MÓDULO DE RESERVAS - CHIFA MATSUE

## 📋 Descripción
Sistema de reservas online para clientes que permite reservar mesas con validación de horarios, anticipación y disponibilidad.

---

## 🗂️ Estructura de Archivos

```
/api/reservas/
├── crear.php              # Endpoint para crear nuevas reservas

/js/reservas/
├── cliente.js             # Lógica frontend del formulario

/reservas.php              # Página pública de reservas
```

---

## 🔧 Configuración de Base de Datos

### Tablas Requeridas
- `Reserva_Config` - Configuración global del sistema
- `Reserva` - Tabla principal de reservas
- `Reserva_Historial` - Auditoría de cambios
- `Cliente` - Información de clientes
- `Mesa` - Mesas disponibles

**IMPORTANTE**: Estas tablas deben estar creadas en la BD `db_restaurante` antes de usar el sistema.

---

## 🚀 Funcionalidades Implementadas

### ✅ FASE 1: Cliente puede hacer reserva

#### Frontend (reservas.php)
- Formulario con validación HTML5
- Campos: nombre, correo, teléfono, fecha, hora, personas, comentarios
- Validación de fecha mínima automática
- Validación de horarios (11:00 AM - 10:00 PM)

#### JavaScript (cliente.js)
- Validación del lado del cliente
- Prevención de fechas pasadas
- Validación de horarios de atención
- Comunicación asíncrona con API
- Manejo de errores y respuestas

#### API (crear.php)
- ✅ Validación de campos obligatorios
- ✅ Validación de anticipación mínima (2 horas)
- ✅ Validación de anticipación máxima (30 días)
- ✅ Validación de horarios de atención
- ✅ Creación/actualización de cliente
- ✅ Generación de token único de cancelación
- ✅ Inserción en tabla Reserva
- ✅ Registro en historial de auditoría
- ✅ Respuesta JSON estandarizada

---

## 🔐 Validaciones Implementadas

### Backend (PHP)
1. **Campos obligatorios**: nombre, correo, teléfono, fecha, hora, personas
2. **Formato de correo**: validación con filter_var()
3. **Anticipación mínima**: 2 horas desde ahora
4. **Anticipación máxima**: 30 días
5. **Horario de atención**: 11:00 AM - 10:00 PM
6. **Rango de comensales**: 1-20 personas
7. **Fechas pasadas**: no permitidas

### Frontend (JavaScript)
1. **Validación de campos vacíos**
2. **Formato de email**
3. **Longitud mínima de campos**
4. **Fecha mínima dinámica**
5. **Validación de horarios**

---

## 📡 Endpoints API

### POST /api/reservas/crear.php

**Descripción**: Crea una nueva reserva

**Parámetros** (FormData):
```
nombre: string (requerido, 3-150 chars)
correo: string (requerido, formato email)
telefono: string (requerido, min 7 chars)
fecha: date (requerido, formato YYYY-MM-DD)
hora: time (requerido, formato HH:MM)
personas: int (requerido, 1-20)
comentarios: string (opcional)
```

**Respuesta Exitosa** (200):
```json
{
  "success": true,
  "message": "¡Reserva creada exitosamente!",
  "data": {
    "reserva_id": 1,
    "token": "a1b2c3d4e5f6...",
    "fecha": "2024-02-15",
    "hora": "19:00",
    "nombre": "Juan Pérez",
    "num_comensales": 4,
    "estado": "Pendiente"
  }
}
```

**Respuesta Error** (422/500):
```json
{
  "success": false,
  "message": "Mensaje de error",
  "errors": {
    "campo": "Error específico"
  }
}
```

---

## 🔄 Flujo de Reserva

```
1. Cliente completa formulario en reservas.php
   ↓
2. JavaScript valida datos del lado del cliente
   ↓
3. Envía POST a /api/reservas/crear.php
   ↓
4. API valida:
   - Campos obligatorios
   - Anticipación mínima/máxima
   - Horarios de atención
   - Rango de comensales
   ↓
5. Busca/crea cliente en BD
   ↓
6. Genera token único (32 chars hex)
   ↓
7. Inserta en tabla Reserva (Estado: Pendiente)
   ↓
8. Registra en Reserva_Historial
   ↓
9. Retorna respuesta JSON con datos de la reserva
   ↓
10. JavaScript muestra mensaje de éxito/error
```

---

## 📊 Estados de Reserva

- **Pendiente**: Recién creada, esperando confirmación
- **Confirmada**: Admin confirmó y asignó mesa
- **Completada**: Cliente llegó y fue atendido
- **Cancelada_Cliente**: Cliente canceló con token
- **Cancelada_Admin**: Admin canceló desde backoffice
- **No_Show**: Cliente no se presentó

---

## 🛡️ Seguridad

1. **PDO Prepared Statements**: Prevención de SQL Injection
2. **Validación de entrada**: Sanitización con htmlspecialchars()
3. **Tokens únicos**: bin2hex(random_bytes(16)) - 32 caracteres
4. **Headers CORS**: Configurados en API
5. **Error logging**: Errores registrados sin exponer detalles al cliente

---

## 🔧 Configuración

### Modificar configuración en BD:
```sql
UPDATE Reserva_Config SET
  Anticipacion_Min_Hrs = 2,
  Anticipacion_Max_Dias = 30,
  Tolerancia_Min = 15,
  Duracion_Estimada_Min = 90,
  Max_Comensales = 20,
  Requiere_Confirmacion = 1
WHERE Estado = 1;
```

### Modificar horarios en código:
- **JavaScript**: `CONFIG.HORARIO_APERTURA` y `CONFIG.HORARIO_CIERRE`
- **PHP**: Validación en líneas 114-126 de crear.php

---

## 🧪 Testing

### Para probar localmente:

1. Asegúrate de tener XAMPP corriendo
2. BD `db_restaurante` creada con tablas de reservas
3. Accede a: `http://localhost/ver-backup/reservas.php`
4. Completa el formulario con datos válidos
5. Verifica en phpMyAdmin que se insertó en tabla `Reserva`

### Casos de prueba:

✅ Reserva válida con anticipación de 3 horas
✅ Reserva para fecha futura (5 días)
❌ Reserva con menos de 2 horas de anticipación
❌ Reserva fuera de horario (9:00 AM o 11:00 PM)
❌ Reserva con fecha pasada
❌ Campos vacíos

---

## 📝 TODOs - Próximas Fases

### FASE 2: Panel Admin
- [ ] Crear `/views/admin/reservas/index.php`
- [ ] Listar reservas del día
- [ ] Confirmar/cancelar reservas
- [ ] Asignar mesas

### FASE 3: Notificaciones
- [ ] Envío de correos de confirmación
- [ ] Integración con WhatsApp API
- [ ] Recordatorios automáticos

### FASE 4: Cliente Autenticado
- [ ] Endpoint: mis-reservas.php
- [ ] Historial de reservas del cliente
- [ ] Cancelación desde perfil

---

## 👥 Equipo

**Desarrollado por**: [Tu equipo]
**Fecha**: Enero 2025
**Versión**: 1.0.0

---

## 📞 Soporte

Para dudas o problemas:
- Revisa los logs de error de PHP
- Verifica la configuración de BD en `config/Database.php`
- Comprueba que las tablas estén creadas correctamente
