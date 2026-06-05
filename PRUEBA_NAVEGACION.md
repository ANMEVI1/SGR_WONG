# PRUEBA DE NAVEGACIÓN - MENÚ UNIFICADO

## Estructura de Directorios:
```
views/admin/
├── dashboard.php                    (nivel 0 - raíz admin)
├── components/
│   └── sidebar.php                  (componente compartido)
├── pos/
│   ├── clientes.php                (nivel 1)
│   ├── mesas.php                   (nivel 1)
│   └── metodos-pago.php            (nivel 1)
├── menu/
│   ├── index.php                   (nivel 1)
│   ├── categorias.php              (nivel 1)
│   └── form.php                    (nivel 1)
├── personal/
│   ├── empleados.php               (nivel 1)
│   ├── roles.php                   (nivel 1 - por crear)
│   └── turnos.php                  (nivel 1 - por crear)
└── inventario/
    ├── insumos.php                 (nivel 1 - por crear)
    └── proveedores.php             (nivel 1)
```

## Sistema de Rutas:

### Desde `dashboard.php` (nivel 0):
- Inclusión: `<?php include 'components/sidebar.php'; ?>`
- $baseUrl = '' (vacío)
- Rutas: `pos/clientes.php`, `menu/index.php`, etc.

### Desde subdirectorios (nivel 1):
- Inclusión: `<?php include '../components/sidebar.php'; ?>`
- $baseUrl = '../'
- Rutas: `../dashboard.php`, `../pos/clientes.php`, etc.

## Verificación de Rutas:

### ✅ Dashboard → Clientes:
- URL: `pos/clientes.php`
- Resultado: `/views/admin/pos/clientes.php`

### ✅ Clientes → Dashboard:
- URL: `../dashboard.php`
- Resultado: `/views/admin/dashboard.php`

### ✅ Clientes → Mesas:
- URL: `../pos/mesas.php`
- Resultado: `/views/admin/pos/mesas.php`

### ✅ Clientes → Empleados:
- URL: `../personal/empleados.php`
- Resultado: `/views/admin/personal/empleados.php`

### ✅ Empleados → Clientes:
- URL: `../pos/clientes.php`
- Resultado: `/views/admin/pos/clientes.php`

## Detección Automática de Página Activa:

```php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
```

### Ejemplos:
- En `dashboard.php`: 
  - $currentPage = 'dashboard.php'
  - $currentDir = 'admin'
  
- En `pos/clientes.php`:
  - $currentPage = 'clientes.php'
  - $currentDir = 'pos'
  
- En `menu/index.php`:
  - $currentPage = 'index.php'
  - $currentDir = 'menu'

## TODOS los CRUDs en el Menú:

### ✅ IMPLEMENTADOS:
1. Dashboard
2. Clientes
3. Mesas
4. Métodos de Pago
5. Platos (Menú)
6. Categorías
7. Empleados
8. Proveedores

### 🔄 PENDIENTES (Visibles en menú):
9. Roles
10. Turnos
11. Insumos

### 📊 TOTAL: 11 opciones de navegación

## Prueba Manual:

1. Abrir: `http://localhost/views/admin/dashboard.php`
2. Verificar que se vean TODOS los menús
3. Click en "Clientes" → debe abrir `pos/clientes.php`
4. Verificar que "Clientes" esté marcado como activo
5. Click en "Empleados" → debe abrir `personal/empleados.php`
6. Verificar que "Empleados" esté marcado como activo
7. Click en "Categorías" → debe abrir `menu/categorias.php`
8. Verificar que "Categorías" esté marcado como activo
9. Click en "Dashboard" → debe volver a `dashboard.php`
10. Verificar que "Dashboard" esté marcado como activo

## Resultado Esperado:
- ✅ Navegación fluida sin errores 404
- ✅ Página activa siempre marcada correctamente
- ✅ Todos los botones visibles en todas las páginas
- ✅ Sin cambios en el menú al navegar
