# 📱 CÓDIGOS QR PARA PAGOS

## 📋 Instrucciones

Los códigos QR para Yape y Plin deben colocarse en esta carpeta.

### Archivos requeridos:

```
/assets/qr/
├── yape-qr.png    ← QR de Yape
├── plin-qr.png    ← QR de Plin
└── README.md      ← Este archivo
```

---

## 🔧 Cómo generar los QR

### Para YAPE:
1. Abre la app de Yape
2. Ve a tu perfil
3. Selecciona "Mi QR"
4. Toma screenshot o descarga el QR
5. Guárdalo como `yape-qr.png` en esta carpeta

### Para PLIN:
1. Abre la app de Plin
2. Ve a "Cobrar"
3. Selecciona "Mi código QR"
4. Toma screenshot o descarga el QR
5. Guárdalo como `plin-qr.png` en esta carpeta

---

## 📏 Especificaciones de Imagen

- **Formato:** PNG (recomendado) o JPG
- **Tamaño mínimo:** 400x400 px
- **Tamaño recomendado:** 800x800 px
- **Fondo:** Preferiblemente blanco
- **Calidad:** Alta resolución para escaneo correcto

---

## 🎨 Placeholder Temporal

Mientras no tengas los QR reales, el sistema mostrará un placeholder genérico.

Para crear un placeholder simple:
1. Crea una imagen de 800x800px con fondo blanco
2. Agrega el logo de Yape/Plin en el centro
3. Guárdala con el nombre correspondiente

---

## 🔐 Seguridad

**IMPORTANTE:** 
- ❌ NO subas estos archivos a repositorios públicos
- ❌ NO compartas los QR en redes sociales
- ✅ Mantenlos solo en tu servidor local/producción
- ✅ Actualízalos si cambias de número o cuenta

---

## 📝 Configuración en el Código

Los QR se configuran en:
`/js/modules/modal-confirmacion-reserva.js`

```javascript
const METODOS_PAGO = {
    yape: {
        qr: 'assets/qr/yape-qr.png',  // ← Ruta del QR
        // ...
    },
    plin: {
        qr: 'assets/qr/plin-qr.png',  // ← Ruta del QR
        // ...
    }
};
```

---

## ✅ Verificación

Para verificar que los QR funcionan:

1. Accede a: `http://localhost/reservas.php`
2. Completa una reserva de prueba
3. En el modal, verifica que se muestren los QR
4. Escanéalos con tu celular para probar

---

**Última actualización:** Enero 2025
