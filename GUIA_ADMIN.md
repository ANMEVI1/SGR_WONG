# 🎯 SISTEMA DE GESTIÓN DE CONTENIDO WEB - CHIFA MATSUE

## 📋 RESUMEN EJECUTIVO

Este sistema permite al ADMINISTRADOR gestionar TODO el contenido de la web SIN tocar código:
- ✅ Crear/Editar/Ocultar PLATOS
- ✅ Marcar platos como TOP VENTAS
- ✅ Marcar platos como PROMOCIONES
- ✅ Todo aparece AUTOMÁTICAMENTE en la web

---

## 🔧 PASO 1: CONFIGURACIÓN INICIAL (SOLO UNA VEZ)

### **Ejecutar en phpMyAdmin:**

1. Abrir phpMyAdmin: http://localhost/phpmyadmin
2. Seleccionar base de datos: `db_restaurante`
3. Click en pestaña "SQL"
4. Copiar y pegar el contenido del archivo: `EJECUTAR_ESTO.sql`
5. Click en "Continuar"

✅ Esto crea los campos `Es_Top` y `Es_Promo` en la tabla Plato

---

## 📱 PASO 2: CÓMO USAR EL SISTEMA

### **A. CREAR UN NUEVO PLATO**

1. **Ir a:** `http://localhost/views/admin/menu/index.php`
2. **Click en:** "Nuevo Plato" (botón verde)
3. **Llenar formulario:**
   - **Nombre:** Ej: "Arroz Chaufa Especial"
   - **Descripción:** Ej: "Arroz frito con pollo, cerdo y camarones"
   - **Categoría:** Seleccionar (Sopas, Chaufas, Tallarines, etc.)
   - **Orden:** Número para ordenar en la carta (1, 2, 3...)
   - **Estado:** 
     - ✅ Disponible = Se muestra en la web
     - ❌ Oculto = No se muestra
   - **Top Ventas:** ✅ Marcar si quieres que aparezca en "TOP VENTAS"
   - **Promoción:** ✅ Marcar si quieres que aparezca en "PROMOCIONES"
   - **Imagen:** Subir foto del plato (JPG, PNG, WEBP - máx 2MB)
   - **Variantes:** (OBLIGATORIO - mínimo 1)
     - Ej: Personal - S/ 15.00
     - Ej: Familiar - S/ 28.00
     - Click "Agregar variante" para más opciones

4. **Click en:** "Crear plato"
5. ✅ **LISTO** - El plato ya está en la web automáticamente

---

### **B. EDITAR UN PLATO EXISTENTE**

1. **Ir a:** `http://localhost/views/admin/menu/index.php`
2. **Buscar el plato** en la tabla
3. **Click en:** Botón ✏️ (Editar)
4. **Modificar lo que necesites:**
   - Cambiar nombre, descripción, precio
   - Cambiar imagen
   - Agregar/quitar variantes
   - Marcar/desmarcar Top Ventas o Promoción
5. **Click en:** "Guardar cambios"
6. ✅ **LISTO** - Los cambios se ven inmediatamente en la web

---

### **C. OCULTAR/MOSTRAR UN PLATO**

**Opción 1: Desde el listado**
1. **Ir a:** `http://localhost/views/admin/menu/index.php`
2. **Click en:** Botón 👁️ (ojo) del plato
3. ✅ El plato se oculta de la web (pero no se borra de la BD)

**Opción 2: Editando el plato**
1. Editar el plato
2. Cambiar "Estado" a "Oculto"
3. Guardar cambios

---

### **D. GESTIONAR TOP VENTAS Y PROMOCIONES**

1. **Ir a:** `http://localhost/views/admin/web/contenido.php`

2. **Ver 3 TABS:**
   - 📊 **TOP VENTAS** - Platos marcados como "Top"
   - 🏷️ **PROMOCIONES** - Platos marcados como "Promo"  
   - 🍽️ **MENÚ** - Todos los platos disponibles

3. **Activar/Desactivar con un click:**
   - Cada plato tiene toggles (interruptores)
   - ✅ Verde = Activo
   - ❌ Rojo = Inactivo
   - Los cambios se guardan automáticamente

4. **Editar platos desde aquí:**
   - Click en botón "Editar" para modificar el plato

---

## 🌐 CÓMO SE VE EN LA WEB

### **1. PÁGINA PRINCIPAL (index.php)**

```
┌─────────────────────────────────────┐
│  HERO (Banner principal)            │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│  TOP VENTAS                         │
│  ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐  │
│  │Plato│ │Plato│ │Plato│ │Plato│  │
│  │ Top │ │ Top │ │ Top │ │ Top │  │
│  └─────┘ └─────┘ └─────┘ └─────┘  │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│  PROMOCIONES                        │
│  ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐  │
│  │Promo│ │Promo│ │Promo│ │Promo│  │
│  └─────┘ └─────┘ └─────┘ └─────┘  │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│  MENÚ COMPLETO                      │
│  [Todos] [Sopas] [Chaufas]...      │
│  ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐  │
│  │Plato│ │Plato│ │Plato│ │Plato│  │
│  └─────┘ └─────┘ └─────┘ └─────┘  │
└─────────────────────────────────────┘
```

### **2. PÁGINA LA CARTA (carta.php)**

- Muestra TODOS los platos disponibles
- Con filtros por categoría
- Solo los que tengan Estado = "Disponible"

---

## 🔄 FLUJO AUTOMÁTICO

```
ADMIN CREA PLATO
        ↓
  Guarda en BD
        ↓
  api/menu.php lee BD
        ↓
  js/app.js carga platos
        ↓
  SE MUESTRA EN WEB
```

**NO necesitas:**
- ❌ Editar código
- ❌ Subir archivos por FTP
- ❌ Modificar HTML/JS
- ❌ Reiniciar nada

**Solo necesitas:**
- ✅ Crear/editar platos desde el admin
- ✅ Marcar Top/Promo con un click
- ✅ La web se actualiza SOLA al recargar

---

## 📊 ARQUITECTURA DEL SISTEMA

### **BACKEND (PHP + MySQL)**

```
views/admin/menu/
├── index.php       → Lista todos los platos
├── form.php        → Formulario crear/editar
└── api.php         → Procesa crear/editar/toggle

views/admin/web/
└── contenido.php   → Gestión Top/Promo/Menú

api/
└── menu.php        → API pública para la web
                     Filtros: ?filtro=todos|top|promo
```

### **FRONTEND (JavaScript)**

```
js/app.js
├── cargarSeccion('top')    → Carga TOP VENTAS
├── cargarSeccion('promo')  → Carga PROMOCIONES
└── cargarSeccion('todos')  → Carga MENÚ COMPLETO
```

### **BASE DE DATOS**

```sql
Plato
├── PlatoID
├── Nombre
├── Descripcion
├── Imagen_URL
├── Estado (Disponible/Oculto)
├── Orden (para ordenar)
├── Es_Top (1=sí, 0=no) ← NUEVO
├── Es_Promo (1=sí, 0=no) ← NUEVO
└── CatID

Plato_Variante
├── VarianteID
├── PlatoID
├── Nombre (Personal, Familiar, etc.)
├── Precio_Venta
└── Estado (1=activo, 0=inactivo)
```

---

## ✅ VALIDACIONES DEL SISTEMA

1. **No puedes crear un plato sin:**
   - Nombre
   - Categoría
   - Al menos 1 variante con precio

2. **Las imágenes:**
   - Formatos: JPG, PNG, WEBP
   - Tamaño máximo: 2 MB
   - Si no subes imagen, usa imagen por defecto

3. **Los platos ocultos:**
   - NO se muestran en la web
   - PERO siguen en la BD
   - Puedes reactivarlos cuando quieras

4. **Top Ventas y Promociones:**
   - Un plato puede ser AMBOS (Top Y Promo)
   - Puedes activar/desactivar sin borrar el plato
   - Los cambios son instantáneos

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### **"Error al crear plato"**
✅ **Solución:** Ejecuta `EJECUTAR_ESTO.sql` en phpMyAdmin

### **"No se ve la imagen del plato"**
✅ **Solución:** Verifica que la carpeta existe: `assets/img/platos/`

### **"El plato no aparece en Top Ventas"**
✅ **Solución:** 
1. Verifica que `Es_Top = 1` en la BD
2. Verifica que `Estado = Disponible`
3. Recarga la página web (F5)

### **"Los cambios no se ven en la web"**
✅ **Solución:**
1. Recarga la página con CTRL + F5 (limpia caché)
2. Verifica que el plato tenga Estado = "Disponible"

---

## 📚 ARCHIVOS CLAVE

```
📁 ver-backup/
├── 📄 EJECUTAR_ESTO.sql ← EJECUTAR PRIMERO
├── 📄 GUIA_ADMIN.md ← ESTE ARCHIVO
│
├── 📁 views/admin/menu/
│   ├── index.php ← Listado de platos
│   ├── form.php ← Crear/Editar plato
│   └── api.php ← Backend CRUD
│
├── 📁 views/admin/web/
│   └── contenido.php ← Gestión Top/Promo
│
├── 📁 api/
│   └── menu.php ← API para la web
│
├── 📁 js/
│   └── app.js ← Carga dinámica de platos
│
└── 📁 assets/img/platos/
    └── (aquí se guardan las imágenes)
```

---

## 🎓 CAPACITACIÓN RÁPIDA

**Para el administrador (5 minutos):**

1. ✅ Ejecutar `EJECUTAR_ESTO.sql` (solo una vez)
2. ✅ Entrar a `/views/admin/menu/index.php`
3. ✅ Click en "Nuevo Plato"
4. ✅ Llenar formulario y guardar
5. ✅ Ir a `/views/admin/web/contenido.php`
6. ✅ Marcar como Top o Promo
7. ✅ Abrir `index.php` y ver el resultado

**¡Listo! El admin ya puede gestionar todo el contenido.**

---

## 📞 SOPORTE

Si algo no funciona:
1. Verifica que ejecutaste `EJECUTAR_ESTO.sql`
2. Verifica que el plato tenga Estado = "Disponible"
3. Verifica que tenga al menos 1 variante con precio
4. Recarga la web con CTRL + F5

---

**Desarrollado por:** EQUIPO-FRONT END (SARA, ROBERT Y PEZO)
**Sistema:** Chifa Matsue - Gestión Web Automática
**Versión:** 2.0
