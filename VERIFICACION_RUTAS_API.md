# VERIFICACIÓN DE RUTAS API - PROYECTO RESTAURANTE

## Estructura del Proyecto:
```
backup/ver-backup/
├── api/
│   ├── admin/
│   │   ├── categoria-detalle.php
│   │   ├── cliente-detalle.php
│   │   ├── empleado-detalle.php
│   │   ├── mesa-detalle.php
│   │   ├── metodo-pago-detalle.php          ✅ EXISTE
│   │   ├── proveedor-detalle.php
│   │   ├── guardar-categoria.php
│   │   ├── guardar-cliente.php
│   │   ├── guardar-empleado.php
│   │   ├── guardar-mesa.php
│   │   ├── guardar-metodo-pago.php          ✅ EXISTE
│   │   ├── guardar-proveedor.php
│   │   ├── cambiar-estado-mesa.php
│   │   ├── toggle-estado-metodo.php         ✅ EXISTE
│   │   └── toggle-proveedor.php
│   └── menu.php
└── views/
    └── admin/
        ├── dashboard.php                     (nivel 0)
        ├── pos/
        │   ├── clientes.php                  (nivel 1)
        │   ├── mesas.php                     (nivel 1)
        │   ├── metodos-pago.php              (nivel 1) ← PROBLEMA AQUÍ
        │   └── caja.php                      (nivel 1)
        ├── menu/
        │   ├── index.php                     (nivel 1)
        │   ├── categorias.php                (nivel 1)
        │   └── form.php                      (nivel 1)
        ├── personal/
        │   └── empleados.php                 (nivel 1)
        └── inventario/
            └── proveedores.php               (nivel 1)
```

## CÁLCULO DE RUTAS RELATIVAS:

### Desde archivos en nivel 1 (subdirectorios):
**Ejemplo:** `views/admin/pos/metodos-pago.php`

**Para llegar a API:**
```
metodos-pago.php  (estamos aquí)
       ↓
../    (sube a views/admin/)
../    (sube a views/)
../    (sube a backup/ver-backup/)
api/admin/metodo-pago-detalle.php
```

**Ruta correcta:** `../../../api/admin/metodo-pago-detalle.php` ✅

### Verificación por archivo:

#### ✅ CLIENTES (pos/clientes.php):
```javascript
fetch(`../../../api/admin/cliente-detalle.php?id=${id}`)      // CORRECTO
fetch('../../../api/admin/guardar-cliente.php')               // CORRECTO
```

#### ✅ MESAS (pos/mesas.php):
```javascript
fetch(`../../../api/admin/mesa-detalle.php?id=${id}`)         // CORRECTO
fetch('../../../api/admin/guardar-mesa.php')                  // CORRECTO
fetch('../../../api/admin/cambiar-estado-mesa.php')           // CORRECTO
```

#### ❓ MÉTODOS DE PAGO (pos/metodos-pago.php):
```javascript
fetch(`../../../api/admin/metodo-pago-detalle.php?id=${id}`)  // DEBERÍA SER CORRECTO
fetch('../../../api/admin/guardar-metodo-pago.php')           // DEBERÍA SER CORRECTO
fetch('../../../api/admin/toggle-estado-metodo.php')          // DEBERÍA SER CORRECTO
```

#### ✅ CATEGORÍAS (menu/categorias.php):
```javascript
fetch(`../../../api/admin/categoria-detalle.php?id=${id}`)    // CORRECTO
fetch('../../../api/admin/guardar-categoria.php')             // CORRECTO
```

#### ✅ EMPLEADOS (personal/empleados.php):
```javascript
fetch(`../../../api/admin/empleado-detalle.php?id=${id}`)     // CORRECTO
fetch('../../../api/admin/guardar-empleado.php')              // CORRECTO
```

#### ✅ PROVEEDORES (inventario/proveedores.php):
```javascript
fetch(`../../../api/admin/proveedor-detalle.php?id=${id}`)    // CORRECTO
fetch('../../../api/admin/guardar-proveedor.php')             // CORRECTO
fetch('../../../api/admin/toggle-proveedor.php')              // CORRECTO
```

## TODAS LAS RUTAS SON CORRECTAS ✅

## Posibles causas del error "conexión":

### 1. ❌ Sesión expirada
```php
// En cada API verificar:
startSecureSession();
requireAuth();
requirePermission('backoffice');
```

### 2. ❌ Error en la base de datos
- Tabla Metodo_Pago no existe
- Campo mal escrito
- Permisos de BD

### 3. ❌ Error de PHP no capturado
- Revisar logs de PHP
- error_log() debe mostrar el problema

### 4. ❌ CORS o Headers
```php
header('Content-Type: application/json');
```

### 5. ❌ JSON malformado en respuesta

## SOLUCIÓN IMPLEMENTADA:

### Frontend (metodos-pago.php):
```javascript
// Logging detallado
console.log('Editando método ID:', id);
console.log('Response status:', response.status);
console.log('Data recibida:', data);
```

### Backend (APIs):
```php
// Error logging completo
error_log("Input recibido: " . json_encode($input));
error_log("Procesando: ID={$metodoId}");
error_log("Stack trace: " . $e->getTraceAsString());
```

## PRUEBA DIAGNÓSTICA:

1. Abrir Chrome DevTools (F12)
2. Ir a pestaña "Network"
3. Intentar editar un método de pago
4. Buscar la petición a `metodo-pago-detalle.php`
5. Ver:
   - Status Code (200, 404, 500?)
   - Response (¿qué devuelve?)
   - Headers (¿hay errores?)
   - Console (logs de JavaScript)

## VERIFICACIÓN MANUAL DE RUTAS:

Desde navegador, probar directamente:
```
http://localhost/backup/ver-backup/api/admin/metodo-pago-detalle.php?id=1
```

Debería devolver JSON:
```json
{
  "success": true,
  "metodo": {
    "MetPagID": 1,
    "Nombre": "Efectivo",
    ...
  }
}
```

Si da error 404 → El archivo no existe en esa ruta
Si da error 500 → Hay un error de PHP (revisar logs)
Si da error 403 → Problema de permisos/sesión
Si devuelve JSON → La ruta está CORRECTA
