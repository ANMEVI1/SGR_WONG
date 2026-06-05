# 🔧 GUÍA DE SOLUCIÓN DE PROBLEMAS - RESERVAS

## ❌ Error: "Error de conexión, sin internet"

### Causas posibles:

1. **XAMPP no está corriendo**
   - ✅ Abre XAMPP Control Panel
   - ✅ Inicia Apache
   - ✅ Inicia MySQL

2. **No estás accediendo desde localhost**
   - ❌ NO: Abrir archivo directamente (file:///)
   - ✅ SÍ: Acceder desde http://localhost/ver-backup/reservas.php

3. **Ruta incorrecta del proyecto**
   - Verifica que el proyecto esté en: `C:/xampp/htdocs/ver-backup/`
   - O ajusta la ruta en el navegador según tu configuración

4. **Tablas de BD no existen**
   - Verifica que ejecutaste el script SQL del módulo de reservas
   - Revisa que existan: `Reserva_Config`, `Reserva`, `Reserva_Historial`

---

## 🧪 PASOS PARA DIAGNOSTICAR

### PASO 1: Abrir archivo de prueba
Abre en tu navegador: `http://localhost/ver-backup/test-reservas.html`

### PASO 2: Ejecutar tests

1. **Test 1: Verificar Conexión a BD**
   - Haz clic en "Probar Conexión"
   - ✅ Debe mostrar: "Conexión a base de datos exitosa"
   - ❌ Si falla: XAMPP no está corriendo o BD no existe

2. **Test 2: Verificar Configuración**
   - Haz clic en "Verificar Config"
   - ✅ Debe mostrar la configuración de reservas
   - ❌ Si falla: No ejecutaste el script SQL de reservas

3. **Test 3: Crear Reserva de Prueba**
   - Haz clic en "Crear Reserva Test"
   - ✅ Debe crear una reserva exitosamente
   - ❌ Si falla: Revisa el mensaje de error

4. **Test 4: Verificar Ruta**
   - Se ejecuta automáticamente
   - Verifica que la URL muestre `localhost`

---

## ✅ CHECKLIST PRE-USO

Antes de usar el sistema, verifica:

- [ ] XAMPP está corriendo (Apache + MySQL)
- [ ] Base de datos `db_restaurante` existe
- [ ] Ejecutaste el script SQL v2.0 base
- [ ] Ejecutaste el script SQL de módulo de reservas
- [ ] Tabla `Reserva_Config` tiene 1 registro activo
- [ ] Accedes desde `http://localhost/` (NO file:///)
- [ ] La carpeta del proyecto está en `htdocs`

---

## 🗄️ VERIFICAR BASE DE DATOS

### En phpMyAdmin (http://localhost/phpmyadmin):

1. Selecciona base de datos `db_restaurante`
2. Verifica que existan estas tablas:
   ```
   ✅ Reserva_Config
   ✅ Reserva
   ✅ Reserva_Historial
   ✅ Cliente
   ✅ Mesa
   ```

3. Ejecuta esta consulta para verificar configuración:
   ```sql
   SELECT * FROM Reserva_Config WHERE Estado = 1;
   ```
   - Debe retornar 1 fila
   - Si no retorna nada, ejecuta:
   ```sql
   INSERT INTO Reserva_Config
     (Anticipacion_Min_Hrs, Anticipacion_Max_Dias, Tolerancia_Min,
      Duracion_Estimada_Min, Max_Comensales, Requiere_Confirmacion,
      Mensaje_Confirmacion, Mensaje_Cancelacion)
   VALUES
     (2, 30, 15, 90, 20, 1,
      'Tu reserva está confirmada. Te esperamos!',
      'Tu reserva ha sido cancelada.');
   ```

---

## 🌐 ESTRUCTURA DE RUTAS

Tu proyecto debe estar así:

```
C:/xampp/htdocs/ver-backup/
├── api/
│   └── reservas/
│       ├── crear.php
│       ├── test-conexion.php
│       └── test-config.php
├── js/
│   └── reservas/
│       └── cliente.js
├── config/
│   ├── Database.php
│   ├── Response.php
│   └── Validator.php
├── reservas.php
└── test-reservas.html
```

**URL correcta**: `http://localhost/ver-backup/reservas.php`

---

## 📝 LOGS DE ERROR

Si sigues teniendo problemas:

1. Abre la consola del navegador (F12)
2. Ve a la pestaña "Console"
3. Busca errores en rojo
4. Busca mensajes que empiecen con "Error al enviar reserva:"
5. Copia el mensaje completo y revisa:
   - ¿Qué URL está intentando acceder?
   - ¿Qué error específico muestra?

---

## 🔄 SOLUCIONES RÁPIDAS

### Solución 1: Reiniciar XAMPP
1. Detén Apache y MySQL
2. Espera 5 segundos
3. Inicia nuevamente

### Solución 2: Limpiar caché del navegador
1. Ctrl + Shift + Delete
2. Borrar caché y cookies
3. Refrescar página (F5)

### Solución 3: Verificar puerto Apache
1. En XAMPP, verifica que Apache esté en puerto 80
2. Si dice otro puerto (ej: 8080), accede a:
   `http://localhost:8080/ver-backup/reservas.php`

### Solución 4: Re-ejecutar SQL
1. Abre phpMyAdmin
2. Elimina tablas: `Reserva_Config`, `Reserva`, `Reserva_Historial`
3. Ejecuta nuevamente el script SQL completo

---

## 📞 PREGUNTAS FRECUENTES

**P: ¿Por qué dice "sin internet" si no necesito internet?**
R: Es un mensaje genérico de error de fetch(). En realidad significa que no pudo conectar con la API local.

**P: ¿Funciona sin internet?**
R: Sí, es 100% local. Solo necesitas XAMPP corriendo.

**P: ¿Puedo cambiar el puerto de XAMPP?**
R: Sí, pero debes ajustar las URLs en consecuencia.

**P: ¿Los archivos test-*.php deben estar en producción?**
R: No, son solo para desarrollo. Puedes eliminarlos después.

---

## 🆘 ÚLTIMO RECURSO

Si nada funciona:

1. Captura de pantalla del error en consola (F12)
2. Captura de pantalla de XAMPP Control Panel
3. Captura de pantalla de phpMyAdmin mostrando tablas
4. Copia el resultado del test-reservas.html
5. Comparte esta información para diagnóstico

---

**Última actualización**: Enero 2025
