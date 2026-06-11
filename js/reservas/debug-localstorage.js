/**
 * SCRIPT DE DEBUG - LOCAL STORAGE RESERVAS
 * 
 * Instrucciones:
 * 1. Abre /reservas.php
 * 2. Abre la consola del navegador (F12)
 * 3. Copia y pega este código completo
 * 4. Ejecuta los comandos según necesites
 */

console.log('=== DEBUG LOCAL STORAGE - SISTEMA DE RESERVAS ===\n');

// COMANDO 1: Ver progreso guardado actual
function verProgresoGuardado() {
    const progreso = localStorage.getItem('reserva_progreso');
    if (!progreso) {
        console.log('❌ No hay progreso guardado');
        return null;
    }
    const data = JSON.parse(progreso);
    console.log('✅ Progreso encontrado:');
    console.log('   - Fecha:', data.fecha || 'vacío');
    console.log('   - Hora:', data.hora || 'vacío');
    console.log('   - Personas:', data.personas || 'vacío');
    console.log('   - Método pago:', data.metodo_pago || 'vacío');
    console.log('   - Platos:', data.platos?.length || 0);
    console.log('   - Comentarios:', data.comentarios ? 'sí' : 'no');
    console.log('   - Timestamp:', new Date(data.timestamp).toLocaleString('es-PE'));
    console.log('\n📦 Datos completos:', data);
    return data;
}

// COMANDO 2: Simular guardado manual
function simularGuardado() {
    const progreso = {
        timestamp: new Date().toISOString(),
        tipo: 'yo',
        fecha: '2025-02-01',
        hora: '19:00',
        personas: '4',
        comentarios: 'Prueba manual',
        metodo_pago: 'efectivo',
        platos: []
    };
    localStorage.setItem('reserva_progreso', JSON.stringify(progreso));
    console.log('✅ Progreso de prueba guardado');
    verProgresoGuardado();
}

// COMANDO 3: Limpiar progreso
function limpiarProgreso() {
    localStorage.removeItem('reserva_progreso');
    console.log('🗑️ Progreso eliminado');
}

// COMANDO 4: Verificar tamaño usado
function verTamanoStorage() {
    let total = 0;
    for (let key in localStorage) {
        if (localStorage.hasOwnProperty(key)) {
            total += localStorage[key].length + key.length;
        }
    }
    console.log(`📊 Tamaño total de localStorage: ${(total / 1024).toFixed(2)} KB`);
    console.log(`   Límite típico: ~5120 KB (5 MB)`);
}

// COMANDO 5: Ver todos los datos en localStorage
function verTodoLocalStorage() {
    console.log('📦 Contenido completo de localStorage:');
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        console.log(`   - ${key}:`, localStorage.getItem(key).substring(0, 100) + '...');
    }
}

// COMANDO 6: Monitorear guardado en tiempo real
function monitorearGuardado() {
    console.log('👁️ Monitoreando guardado automático...');
    console.log('   (Cambia algún campo del formulario y observa)');
    
    let lastValue = localStorage.getItem('reserva_progreso');
    
    const interval = setInterval(() => {
        const currentValue = localStorage.getItem('reserva_progreso');
        if (currentValue !== lastValue) {
            console.log('💾 ¡Progreso actualizado!', new Date().toLocaleTimeString());
            lastValue = currentValue;
            verProgresoGuardado();
        }
    }, 500);
    
    // Detener después de 30 segundos
    setTimeout(() => {
        clearInterval(interval);
        console.log('⏹️ Monitoreo detenido');
    }, 30000);
    
    return interval;
}

// Mostrar comandos disponibles
console.log('📝 COMANDOS DISPONIBLES:\n');
console.log('   verProgresoGuardado()    - Ver progreso actual');
console.log('   simularGuardado()        - Guardar progreso de prueba');
console.log('   limpiarProgreso()        - Eliminar progreso guardado');
console.log('   verTamanoStorage()       - Ver espacio usado');
console.log('   verTodoLocalStorage()    - Ver todas las claves');
console.log('   monitorearGuardado()     - Monitorear cambios en tiempo real');
console.log('\n');

// Auto-ejecutar verificación inicial
console.log('🔍 VERIFICACIÓN INICIAL:');
verProgresoGuardado();
console.log('\n');
