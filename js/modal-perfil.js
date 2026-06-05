document.addEventListener("DOMContentLoaded", () => {
  const overlay = document.getElementById("modalOverlay");
  const openBtn = document.getElementById("openProfile");
  const closeBtn = document.getElementById("closeModal");
  const content = document.getElementById("content");
  const menuItems = document.querySelectorAll(".modal-menu-item");
  const toast = document.getElementById("toast");

  if (!overlay || !openBtn || !closeBtn || !content || !toast) {
    return;
  }

  const eyeOpen = `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12Z"/><circle cx="12" cy="12" r="3"/></svg>`;
  const eyeClosed = `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a19.77 19.77 0 0 1 4.22-5.36"/><path d="M9.9 4.24A11 11 0 0 1 12 4c7 0 11 8 11 8a19.5 19.5 0 0 1-3.17 4.19"/><path d="m1 1 22 22"/><path d="M14.12 14.12a3 3 0 0 1-4.24-4.24"/></svg>`;
  const editIcon = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`;
  const plusIcon = `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>`;
  const pinIcon = `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>`;

  const profileData = window.USER_PROFILE || {};
  const profile = {
    nombre: profileData.nombre || "Carlo Andre Mestanza Vinces",
    correo: profileData.correo || "carlo@correo.com",
    tipoDocumento: profileData.tipoDocumento || "DNI",
    documento: profileData.documento || "77534805",
    telefono: profileData.telefono || "937660992",
    direccion: profileData.direccion || "Av. Siempre Viva 742",
  };

  const locationPath = window.location.pathname;
  let appRoot = '/';

  if (locationPath.includes('/views/')) {
    appRoot = locationPath.split('/views/')[0] || '/';
  } else if (locationPath.endsWith('/index.php')) {
    appRoot = locationPath.replace(/\/index\.php$/, '') || '/';
  } else {
    appRoot = locationPath.substring(0, locationPath.lastIndexOf('/') + 1) || '/';
  }

  if (!appRoot.endsWith('/')) {
    appRoot += '/';
  }

  const adminBase = `${appRoot}views/admin`;

  const sections = {
    datos: () => `
      <div class="section-head">
        <div>
          <h2>Datos personales</h2>
          <p>Aquí podrás encontrar tu información personal</p>
        </div>
        <button class="btn btn-outline-red" id="editBtn">${editIcon} Editar</button>
      </div>
      <form class="form-grid" id="datosForm">
        <div class="field full">
          <label>Nombre y Apellidos</label>
          <input type="text" name="nombre_completo" value="${profile.nombre}" readonly />
        </div>
        <div class="field full">
          <label>Correo electrónico</label>
          <input type="email" name="correo" value="${profile.correo}" readonly />
        </div>
        <div class="field">
          <label>Documento</label>
          <div class="row-doc">
            <select name="tipo_documento" id="tipoDocumento" disabled>
              <option${profile.tipoDocumento === "DNI" ? " selected" : ""}>DNI</option>
              <option${profile.tipoDocumento === "CE" ? " selected" : ""}>CE</option>
              <option${profile.tipoDocumento === "RUC" ? " selected" : ""}>RUC</option>
            </select>
            <input type="hidden" id="tipoDocumentoHidden" name="tipo_documento" value="${profile.tipoDocumento}" />
            <input type="text" name="nmr_documento" value="${profile.documento}" readonly />
          </div>
        </div>
        <div class="field">
          <label>Número de teléfono</label>
          <input type="tel" name="telefono" value="${profile.telefono}" readonly />
        </div>
        <div class="field full">
          <label>Dirección de domicilio</label>
          <input type="text" name="direccion" value="${profile.direccion}" readonly />
        </div>
        <div class="actions full">
          <button type="submit" class="btn btn-primary" id="saveDatos" disabled>Guardar cambios</button>
        </div>
      </form>
    `,
    
    reservas: () => `
      <div class="section-head">
        <div>
          <h2>Mis Reservas</h2>
          <p>Consulta el estado de tus reservas activas</p>
        </div>
        <button class="btn btn-ghost-red" id="btnNuevaReserva">
          ${plusIcon} Nueva Reserva
        </button>
      </div>
      <div id="reservasContainer">
        <div class="loading-state">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
          </svg>
          <p>Cargando reservas...</p>
        </div>
      </div>
    `,
    
    pedidos: () => `
      <div class="section-head">
        <div>
          <h2>Mis pedidos</h2>
          <p>Revisa el historial de tus compras</p>
        </div>
      </div>
      <div class="empty-state">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        Aún no tienes pedidos registrados
      </div>
    `,
    
    direcciones: () => `
      <div class="section-head">
        <div>
          <h2>Direcciones</h2>
          <p>Gestiona tus direcciones de envío</p>
        </div>
        <button class="btn btn-ghost-red" id="addAddr">${plusIcon} Agregar dirección</button>
      </div>
      <div class="empty-state">
        ${pinIcon} Aún no tienes direcciones registradas
      </div>
    `,
    
    password: () => `
      <div class="section-head">
        <div>
          <h2>Cambiar Contraseña</h2>
          <p>Aquí podrás modificar tu contraseña</p>
        </div>
      </div>
      <form class="form-grid" id="pwForm">
        <div class="field full">
          <label>Contraseña actual<span class="req">*</span></label>
          <div class="input-wrap">
            <input type="password" name="current_password" placeholder="Ingresa la contraseña actual" autocomplete="current-password" />
            <button type="button" class="eye" data-eye>${eyeClosed}</button>
          </div>
        </div>
        <div class="field full">
          <label>Nueva contraseña<span class="req">*</span></label>
          <div class="input-wrap">
            <input type="password" name="new_password" placeholder="Ingresa la nueva contraseña" autocomplete="new-password" />
            <button type="button" class="eye" data-eye>${eyeClosed}</button>
          </div>
        </div>
        <div class="field full">
          <label>Confirmar contraseña<span class="req">*</span></label>
          <div class="input-wrap">
            <input type="password" name="confirm_password" placeholder="Confirmar la nueva contraseña" autocomplete="new-password" />
            <button type="button" class="eye" data-eye>${eyeClosed}</button>
          </div>
        </div>
        <div class="actions full">
          <button type="submit" class="btn btn-primary" id="savePw">Guardar</button>
        </div>
      </form>
    `,
    
    logout: () => {
      performLogout();
      return `<div class="empty-state">Cerrando sesión…</div>`;
    },
    
    admin: () => `
      <div class="section-head">
        <div>
          <h2>Panel de Administración</h2>
          <p>Gestiona el sistema del restaurante</p>
        </div>
      </div>
      <div class="admin-actions">
        <a href="${adminBase}/dashboard.php" class="admin-card">
          <div class="admin-icon">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9,22 9,12 15,12 15,22"/>
            </svg>
          </div>
          <h3>Dashboard</h3>
          <p>Vista general del sistema</p>
        </a>
        <a href="${adminBase}/usuarios/index.php" class="admin-card">
          <div class="admin-icon">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="9" cy="8" r="4"/>
              <path d="M17 11a3 3 0 1 0 0-6"/>
              <path d="M2 21a7 7 0 0 1 14 0"/>
            </svg>
          </div>
          <h3>Usuarios</h3>
          <p>Gestionar usuarios del sistema</p>
        </a>
        <a href="${adminBase}/menu/index.php" class="admin-card">
          <div class="admin-icon">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
              <path d="M3 6h18"/>
              <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
          </div>
          <h3>Menú</h3>
          <p>Administrar platos y categorías</p>
        </a>
        <a href="${adminBase}/pedidos/index.php" class="admin-card">
          <div class="admin-icon">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <path d="M12 6v6l4 2"/>
            </svg>
          </div>
          <h3>Pedidos</h3>
          <p>Gestionar pedidos activos</p>
        </a>
      </div>
    `
  };

  function render(section) {
    console.log('=== RENDER LLAMADO ===');
    console.log('Section:', section);
    console.log('sections[section] existe?', typeof sections[section]);
    
    if (!sections[section]) {
      console.error('Sección no encontrada:', section);
      return;
    }
    
    content.innerHTML = sections[section]();
    console.log('HTML renderizado en content');
    
    menuItems.forEach((m) => m.classList.toggle("active", m.dataset.section === section));
    console.log('Menú items actualizados');
    
    console.log('Llamando a bindSection...');
    bindSection(section);
  }

  function bindSection(section) {
    if (section === "datos") {
      const editBtn = document.getElementById("editBtn");
      const saveBtn = document.getElementById("saveDatos");
      const form = document.getElementById("datosForm");
      const inputs = content.querySelectorAll("#datosForm input:not([type='hidden']), #datosForm select");
      const tipoSelect = document.getElementById("tipoDocumento");
      const tipoHidden = document.getElementById("tipoDocumentoHidden");
      let editing = false;

      const toggleEditing = (value) => {
        editing = value;
        inputs.forEach((input) => {
          if (input.tagName === "SELECT") {
            input.disabled = !value;
          } else {
            input.readOnly = !value;
          }
        });

        if (tipoHidden) {
          tipoHidden.disabled = value;
        }

        saveBtn.disabled = !value;
        editBtn.innerHTML = editing ? `${editIcon} Cancelar` : `${editIcon} Editar`;
      };

      editBtn?.addEventListener("click", (event) => {
        event.preventDefault();
        toggleEditing(!editing);
      });

      tipoSelect?.addEventListener("change", () => {
        if (tipoHidden) {
          tipoHidden.value = tipoSelect.value;
        }
      });

      form?.addEventListener("submit", async (event) => {
        event.preventDefault();
        if (!editing) {
          return;
        }

        saveBtn.disabled = true;
        editBtn.disabled = true;

        const formData = new FormData(form);
        if (tipoSelect) {
          formData.set("tipo_documento", tipoSelect.value);
        }

        try {
          const response = await fetch("procesos_backend/actualizar_perfil.php", {
            method: "POST",
            body: formData,
            credentials: "same-origin",
          });
          const result = await response.json();

          if (result.success) {
            showToast(result.message || "Perfil actualizado");
            toggleEditing(false);
          } else {
            showToast(result.message || "No se pudieron guardar los cambios");
          }
        } catch (error) {
          console.error(error);
          showToast("Error al conectar con el servidor");
        } finally {
          saveBtn.disabled = !editing;
          editBtn.disabled = false;
        }
      });
    }

    if (section === "direcciones") {
      document.getElementById("addAddr")?.addEventListener("click", () => {
        showToast("Formulario de dirección (demo)");
      });
    }

    if (section === "reservas") {
      console.log('=== SECCIÓN RESERVAS ACTIVADA ===');
      console.log('appRoot:', appRoot);
      
      const btnNuevaReserva = document.getElementById("btnNuevaReserva");
      if (btnNuevaReserva) {
        console.log('Botón Nueva Reserva encontrado');
        btnNuevaReserva.addEventListener("click", () => {
          console.log('Click en Nueva Reserva');
          window.location.href = `${appRoot}reservas.php`;
        });
      } else {
        console.error('Botón Nueva Reserva NO encontrado');
      }
      
      console.log('Llamando a cargarReservas()');
      cargarReservas();
    }

    if (section === "password") {
      content.querySelectorAll("[data-eye]").forEach((btn) => {
        btn.addEventListener("click", () => {
          const input = btn.parentElement.querySelector("input");
          const isPw = input.type === "password";
          input.type = isPw ? "text" : "password";
          btn.innerHTML = isPw ? eyeOpen : eyeClosed;
        });
      });

      const form = document.getElementById("pwForm");
      const currentInput = form?.querySelector("input[name='current_password']");
      const newInput = form?.querySelector("input[name='new_password']");
      const confirmInput = form?.querySelector("input[name='confirm_password']");
      const saveBtn = document.getElementById("savePw");

      form?.addEventListener("submit", async (event) => {
        event.preventDefault();
        if (!currentInput || !newInput || !confirmInput) return;

        const currentPassword = currentInput.value.trim();
        const newPassword = newInput.value.trim();
        const confirmPassword = confirmInput.value.trim();

        if (!currentPassword || !newPassword || !confirmPassword) {
          showToast("Completa todos los campos de contraseña");
          return;
        }

        if (newPassword !== confirmPassword) {
          showToast("Las contraseñas no coinciden");
          return;
        }

        saveBtn.disabled = true;

        const formData = new FormData();
        formData.append("current_password", currentPassword);
        formData.append("new_password", newPassword);
        formData.append("confirm_password", confirmPassword);

        try {
          const response = await fetch("procesos_backend/cambiar_contrasena.php", {
            method: "POST",
            body: formData,
            credentials: "same-origin",
          });
          const result = await response.json();

          if (result.success) {
            showToast(result.message || "Contraseña actualizada");
            currentInput.value = "";
            newInput.value = "";
            confirmInput.value = "";
            content.querySelectorAll("[data-eye]").forEach((btn) => {
              const input = btn.parentElement.querySelector("input");
              input.type = "password";
              btn.innerHTML = eyeClosed;
            });
          } else {
            showToast(result.message || "No se pudo cambiar la contraseña");
          }
        } catch (error) {
          console.error(error);
          showToast("Error al conectar con el servidor");
        } finally {
          saveBtn.disabled = false;
        }
      });
    }

    if (section === "logout") {
      performLogout();
    }
  }

  async function performLogout() {
    try {
      const response = await fetch("procesos_backend/cerrar_sesion_ajax.php", {
        method: "POST",
        credentials: "same-origin",
      });
      const result = await response.json();

      if (result.success) {
        closeModal();
        showToast(result.message || "Sesión cerrada");
        setTimeout(() => {
          window.location.href = "index.php";
        }, 900);
      } else {
        showToast(result.message || "No se pudo cerrar sesión");
      }
    } catch (error) {
      console.error(error);
      showToast("Error al cerrar sesión");
    }
  }

  function openModal() {
    overlay.classList.add("open");
    overlay.setAttribute("aria-hidden", "false");
    document.body.classList.add("modal-open");
    render("datos");
  }
  
  function closeModal() {
    overlay.classList.remove("open");
    overlay.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-open");
  }

  let toastTimer;
  function showToast(msg) {
    toast.textContent = msg;
    toast.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove("show"), 2200);
  }

  async function cargarReservas() {
    console.log('=== INICIANDO CARGA DE RESERVAS ===');
    const container = document.getElementById("reservasContainer");
    
    if (!container) {
      console.error('ERROR: Container reservasContainer NO encontrado');
      return;
    }
    
    console.log('Container encontrado:', container);
    const apiUrl = `${appRoot}api/reservas/mis-reservas.php`;
    console.log('URL de API:', apiUrl);

    try {
      console.log('Iniciando fetch...');
      const response = await fetch(apiUrl, {
        method: "GET",
        credentials: "same-origin"
      });
      
      console.log('Response status:', response.status);
      console.log('Response ok:', response.ok);
      
      const result = await response.json();
      console.log('Result:', result);

      if (result.success && result.data && result.data.length > 0) {
        console.log('Mostrando', result.data.length, 'reservas');
        mostrarReservas(result.data, container);
      } else {
        console.log('Sin reservas, mostrando empty state');
        container.innerHTML = `
          <div class="empty-state">
            <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <path d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            <p>Aún no tienes reservas registradas</p>
            <button class="btn btn-primary" onclick="window.location.href='${appRoot}reservas.php'">Hacer mi primera reserva</button>
          </div>
        `;
      }
    } catch (error) {
      console.error("ERROR cargando reservas:", error);
      console.error("Error completo:", error.message, error.stack);
      container.innerHTML = `
        <div class="empty-state">
          <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 8v4M12 16h.01"/>
          </svg>
          <p>Error al cargar tus reservas</p>
          <p style="font-size: 12px; color: #999;">${error.message}</p>
          <button class="btn btn-outline" onclick="location.reload()">Reintentar</button>
        </div>
      `;
    }
  }

  function mostrarReservas(reservas, container) {
    const html = `
      <div class="reservas-grid">
        ${reservas.map(r => crearTarjetaReserva(r)).join("")}
      </div>
    `;
    container.innerHTML = html;
    
    // Agregar event listeners a los botones de ver más
    container.querySelectorAll('[data-reserva-id]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const reservaId = btn.dataset.reservaId;
        verDetalleReserva(reservaId);
      });
    });
  }

  function crearTarjetaReserva(r) {
    const estadoClasses = {
      "Pendiente": "pendiente",
      "Confirmada": "confirmada",
      "Cancelada": "cancelada",
      "Completada": "completada"
    };
    const estadoClass = estadoClasses[r.estado] || "pendiente";
    const fecha = new Date(r.fecha + "T00:00:00").toLocaleDateString("es-ES", { weekday: "short", year: "numeric", month: "short", day: "numeric" });

    return `
      <div class="reserva-card ${estadoClass}">
        <div class="reserva-card-header">
          <div class="reserva-numero">#${r.id}</div>
          <span class="reserva-badge reserva-badge-${estadoClass}">${r.estado}</span>
        </div>
        <div class="reserva-card-body">
          <div class="reserva-info-row">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <path d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            <span>${fecha}</span>
          </div>
          <div class="reserva-info-row">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <path d="M12 6v6l4 2"/>
            </svg>
            <span>${r.hora}</span>
          </div>
          <div class="reserva-info-row">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>${r.personas} ${r.personas === 1 ? "persona" : "personas"}</span>
          </div>
          <button class="btn-ver-detalle" data-reserva-id="${r.id}">Ver más</button>
        </div>
      </div>
    `;
  }

  async function verDetalleReserva(reservaId) {
    const apiUrl = `${appRoot}api/reservas/detalle-reserva.php?id=${reservaId}`;
    
    try {
      const response = await fetch(apiUrl, {
        method: "GET",
        credentials: "same-origin"
      });
      
      const result = await response.json();
      
      if (result.success && result.data) {
        mostrarModalDetalle(result.data);
      } else {
        showToast(result.message || "Error al cargar detalles");
      }
    } catch (error) {
      console.error("Error cargando detalle:", error);
      showToast("Error al cargar detalles de reserva");
    }
  }

  function mostrarModalDetalle(detalle) {
    const estadoClasses = {
      "Pendiente": "pendiente",
      "Confirmada": "confirmada",
      "Cancelada": "cancelada",
      "Completada": "completada"
    };
    const estadoClass = estadoClasses[detalle.estado] || "pendiente";
    const fecha = new Date(detalle.fecha + "T00:00:00").toLocaleDateString("es-ES", { weekday: "long", year: "numeric", month: "long", day: "numeric" });
    
    const platosHTML = detalle.platos.length > 0 ? `
      <div class="detalle-section">
        <h3>Platos Pre-ordenados</h3>
        <table class="tabla-platos">
          <thead>
            <tr>
              <th>Plato</th>
              <th>Cant.</th>
              <th>Precio</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            ${detalle.platos.map(p => `
              <tr>
                <td>${p.nombre}</td>
                <td>${p.cantidad}</td>
                <td>S/ ${p.precio.toFixed(2)}</td>
                <td>S/ ${p.subtotal.toFixed(2)}</td>
              </tr>
            `).join('')}
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3"><strong>Total Platos:</strong></td>
              <td><strong>S/ ${detalle.total_platos.toFixed(2)}</strong></td>
            </tr>
          </tfoot>
        </table>
      </div>
    ` : '';
    
    const modalHTML = `
      <div class="modal-detalle-reserva" id="modalDetalleReserva">
        <div class="modal-detalle-content">
          <button class="modal-detalle-close" id="closeDetalleModal">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6 6 18M6 6l12 12"/>
            </svg>
          </button>
          
          <div class="modal-detalle-header">
            <h2>Reserva #${detalle.id}</h2>
            <span class="reserva-badge reserva-badge-${estadoClass}">${detalle.estado}</span>
          </div>
          
          <div class="detalle-section">
            <h3>Información de la Reserva</h3>
            <div class="detalle-grid">
              <div class="detalle-item">
                <span class="detalle-label">Fecha:</span>
                <span class="detalle-value">${fecha}</span>
              </div>
              <div class="detalle-item">
                <span class="detalle-label">Hora:</span>
                <span class="detalle-value">${detalle.hora}</span>
              </div>
              <div class="detalle-item">
                <span class="detalle-label">Comensales:</span>
                <span class="detalle-value">${detalle.personas} ${detalle.personas === 1 ? 'persona' : 'personas'}</span>
              </div>
              <div class="detalle-item">
                <span class="detalle-label">Señal pagada:</span>
                <span class="detalle-value">S/ ${detalle.monto_senal.toFixed(2)}</span>
              </div>
            </div>
          </div>
          
          ${platosHTML}
          
          ${detalle.observaciones ? `
            <div class="detalle-section">
              <h3>Observaciones</h3>
              <p class="detalle-observaciones">${detalle.observaciones}</p>
            </div>
          ` : ''}
          
          <div class="detalle-section">
            <h3>Datos de Contacto</h3>
            <div class="detalle-grid">
              <div class="detalle-item">
                <span class="detalle-label">Nombre:</span>
                <span class="detalle-value">${detalle.cliente.nombre}</span>
              </div>
              <div class="detalle-item">
                <span class="detalle-label">Teléfono:</span>
                <span class="detalle-value">${detalle.cliente.telefono}</span>
              </div>
              <div class="detalle-item full">
                <span class="detalle-label">Correo:</span>
                <span class="detalle-value">${detalle.cliente.correo}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
    
    // Agregar modal al body
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);
    
    // Agregar event listener para cerrar
    setTimeout(() => {
      const modal = document.getElementById('modalDetalleReserva');
      const closeBtn = document.getElementById('closeDetalleModal');
      
      if (modal) {
        modal.classList.add('show');
      }
      
      closeBtn?.addEventListener('click', () => {
        modal?.classList.remove('show');
        setTimeout(() => modalContainer.remove(), 300);
      });
      
      modal?.addEventListener('click', (e) => {
        if (e.target === modal) {
          modal.classList.remove('show');
          setTimeout(() => modalContainer.remove(), 300);
        }
      });
    }, 10);
  }

  openBtn.addEventListener("click", openModal);
  closeBtn.addEventListener("click", closeModal);
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) closeModal();
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && overlay.classList.contains("open")) closeModal();
  });
  menuItems.forEach((item) => {
    if (item.dataset.section === "admin") {
      item.addEventListener("click", () => {
        window.location.href = `${adminBase}/dashboard.php`;
      });
    } else {
      item.addEventListener("click", () => render(item.dataset.section));
    }
  });
});
