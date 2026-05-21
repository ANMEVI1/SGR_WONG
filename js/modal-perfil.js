document.addEventListener("DOMContentLoaded", () => {
  const overlay = document.getElementById("modalOverlay");
  const openBtn = document.getElementById("openProfile");
  const closeBtn = document.getElementById("closeModal");
  const content = document.getElementById("content");
  const toast = document.getElementById("toast");
  const profileContainer = document.querySelector(".user-logged");
  const openTrigger = openBtn || profileContainer;

  if (!overlay || !closeBtn || !content || !toast) return;

  const endpoints = {
    perfil: "procesos_backend/actualizar_perfil.php",
    password: "procesos_backend/cambiar_contrasena.php",
    logout: "procesos_backend/cerrar_sesion.php?ajax=1",
  };

  const escapeHtml = (value) =>
    String(value ?? "").replace(/[&<>"'`=\\/]/g, (s) => ({
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#39;",
      "`": "&#96;",
      "=": "&#61;",
      "/": "&#47;",
    }[s]));

  const eyeOpen = `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12Z"/><circle cx="12" cy="12" r="3"/></svg>`;
  const eyeClosed = `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a19.77 19.77 0 0 1 4.22-5.36"/><path d="M9.9 4.24A11 11 0 0 1 12 4c7 0 11 8 11 8a19.5 19.5 0 0 1-3.17 4.19"/><path d="m1 1 22 22"/><path d="M14.12 14.12a3 3 0 0 1-4.24-4.24"/></svg>`;
  const editIcon = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`;
  const plusIcon = `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>`;
  const pinIcon = `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>`;

  const profileData = window.USER_PROFILE || {
    nombre: "",
    correo: "",
    tipoDocumento: "DNI",
    documento: "",
    telefono: "",
    direccion: "",
  };

  const menuConfig = Array.isArray(window.USER_MENU) && window.USER_MENU.length
    ? window.USER_MENU
    : [
        { section: "datos", label: "Mis datos" },
        { section: "pedidos", label: "Mis pedidos" },
        { section: "direcciones", label: "Direcciones" },
        { section: "password", label: "Contraseña" },
        { section: "logout", label: "Cerrar sesión" },
      ];

  const sections = {
    datos: () => `
      <div class="section-head">
        <div>
          <h2>Datos personales</h2>
          <p>Aquí podrás encontrar tu información personal</p>
        </div>
        <button type="button" class="btn btn-outline-red" id="editBtn">${editIcon} Editar</button>
      </div>
      <form class="form-grid" id="datosForm">
        <div class="field full">
          <label>Nombre y Apellidos</label>
          <input type="text" name="nombre_completo" value="${escapeHtml(profileData.nombre)}" readonly />
        </div>
        <div class="field full">
          <label>Correo electrónico</label>
          <input type="email" name="correo" value="${escapeHtml(profileData.correo)}" readonly />
        </div>
        <div class="field">
          <label>Documento</label>
          <div class="row-doc">
            <input type="hidden" name="tipo_documento" id="tipoDocumentoHidden" value="${escapeHtml(profileData.tipoDocumento)}" />
            <select id="tipoDocumento" disabled>
              <option value="DNI" ${profileData.tipoDocumento === "DNI" ? "selected" : ""}>DNI</option>
              <option value="CE" ${profileData.tipoDocumento === "CE" ? "selected" : ""}>CE</option>
              <option value="RUC" ${profileData.tipoDocumento === "RUC" ? "selected" : ""}>RUC</option>
            </select>
            <input type="text" name="num_documento" id="numDocumento" value="${escapeHtml(profileData.documento)}" readonly />
          </div>
        </div>
        <div class="field">
          <label>Número de teléfono</label>
          <input type="tel" name="telefono" value="${escapeHtml(profileData.telefono)}" readonly />
        </div>
        <div class="field full">
          <label>Dirección de domicilio</label>
          <input type="text" name="direccion" value="${escapeHtml(profileData.direccion)}" readonly />
        </div>
        <div class="actions full">
          <button type="submit" class="btn btn-primary" id="saveDatos" disabled>Guardar cambios</button>
        </div>
      </form>
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
        <button type="button" class="btn btn-ghost-red" id="addAddr">${plusIcon} Agregar dirección</button>
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
            <input type="password" name="actual" id="passActual" placeholder="Ingresa la contraseña actual" required />
            <button type="button" class="eye" data-eye>${eyeClosed}</button>
          </div>
        </div>
        <div class="field full">
          <label>Nueva contraseña<span class="req">*</span></label>
          <div class="input-wrap">
            <input type="password" name="nueva" id="passNueva" placeholder="Ingresa la nueva contraseña" required />
            <button type="button" class="eye" data-eye>${eyeClosed}</button>
          </div>
        </div>
        <div class="field full">
          <label>Repetir contraseña<span class="req">*</span></label>
          <div class="input-wrap">
            <input type="password" name="repetir" id="passRepetir" placeholder="Repetir la nueva contraseña" required />
            <button type="button" class="eye" data-eye>${eyeClosed}</button>
          </div>
        </div>
        <div class="actions full">
          <button type="submit" class="btn btn-primary" id="savePw">Guardar</button>
        </div>
      </form>
    `,
    logout: () => `<div class="empty-state">Cerrando sesión…</div>`,
  };

  let toastTimer;

  function showToast(msg) {
    toast.textContent = msg;
    toast.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove("show"), 2200);
  }

  function toggleInputs(inputs, enable) {
    inputs.forEach((input) => {
      if (input.tagName.toLowerCase() === "select") input.disabled = !enable;
      else input.readOnly = !enable;
    });
  }

  async function submitForm(url, formData) {
    try {
      const response = await fetch(url, {
        method: "POST",
        body: formData,
        credentials: "same-origin",
      });
      return await response.json();
    } catch {
      return { success: false, message: "Error de conexión. Intenta de nuevo." };
    }
  }

  async function handleLogout() {
    showToast("Cerrando sesión…");
    try {
      const response = await fetch(endpoints.logout, {
        method: "GET",
        credentials: "same-origin",
      });
      const data = await response.json();
      if (data.success) {
        closeModal();
        setTimeout(() => {
          window.location.href = "login.php";
        }, 500);
      } else {
        showToast(data.message || "No se pudo cerrar sesión.");
      }
    } catch {
      showToast("Error al cerrar sesión. Intenta de nuevo.");
    }
  }

  function bindSection(section) {
    if (section === "datos") {
      const editBtn = document.getElementById("editBtn");
      const saveBtn = document.getElementById("saveDatos");
      const form = document.getElementById("datosForm");
      const inputs = content.querySelectorAll("#datosForm input, #datosForm select");
      const tipoSelect = document.getElementById("tipoDocumento");
      const tipoHidden = document.getElementById("tipoDocumentoHidden");
      let editing = false;

      const setEditing = (value) => {
        editing = value;
        toggleInputs(inputs, value);
        saveBtn.disabled = !value;
        editBtn.innerHTML = value ? `${editIcon} Cancelar` : `${editIcon} Editar`;
        if (tipoHidden && tipoSelect) tipoHidden.value = tipoSelect.value;
      };

      editBtn?.addEventListener("click", () => setEditing(!editing));

      tipoSelect?.addEventListener("change", () => {
        if (tipoHidden) tipoHidden.value = tipoSelect.value;
      });

      form?.addEventListener("submit", async (event) => {
        event.preventDefault();

        const nombre = form.querySelector('[name="nombre_completo"]')?.value.trim();
        const correo = form.querySelector('[name="correo"]')?.value.trim();
        const documento = form.querySelector('[name="num_documento"]')?.value.trim();
        const telefono = form.querySelector('[name="telefono"]')?.value.trim();
        const tipo = tipoHidden?.value || "DNI";

        if (!nombre || !correo || !documento || !telefono) {
          showToast("Completa los campos obligatorios.");
          return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
          showToast("Ingresa un correo válido.");
          return;
        }

        if (!["DNI", "CE", "RUC"].includes(tipo)) {
          showToast("Tipo de documento inválido.");
          return;
        }

        saveBtn.disabled = true;
        const result = await submitForm(endpoints.perfil, new FormData(form));

        if (result.success) {
          showToast(result.message || "Datos guardados correctamente");
          profileData.nombre = form.querySelector('[name="nombre_completo"]')?.value || profileData.nombre;
          profileData.correo = form.querySelector('[name="correo"]')?.value || profileData.correo;
          profileData.tipoDocumento = tipo;
          profileData.documento = documento;
          profileData.telefono = telefono;
          profileData.direccion = form.querySelector('[name="direccion"]')?.value || profileData.direccion;
          setEditing(false);

          const userEmailNode = document.querySelector(".user-email");
          if (userEmailNode && profileData.correo) userEmailNode.textContent = profileData.correo;
        } else {
          showToast(result.message || "No se pudo actualizar la información");
          saveBtn.disabled = false;
        }
      });
    }

    if (section === "direcciones") {
      document.getElementById("addAddr")?.addEventListener("click", () => {
        showToast("Formulario de dirección (demo)");
      });
    }

    if (section === "password") {
      const form = document.getElementById("pwForm");

      content.querySelectorAll("[data-eye]").forEach((btn) => {
        btn.addEventListener("click", () => {
          const input = btn.parentElement.querySelector("input");
          const isPw = input.type === "password";
          input.type = isPw ? "text" : "password";
          btn.innerHTML = isPw ? eyeOpen : eyeClosed;
        });
      });

      form?.addEventListener("submit", async (event) => {
        event.preventDefault();

        const actual = form.querySelector('[name="actual"]')?.value.trim();
        const nueva = form.querySelector('[name="nueva"]')?.value.trim();
        const repetir = form.querySelector('[name="repetir"]')?.value.trim();

        if (!actual || !nueva || !repetir) {
          showToast("Completa todos los campos de contraseña.");
          return;
        }

        if (nueva.length < 6) {
          showToast("La nueva contraseña debe tener al menos 6 caracteres.");
          return;
        }

        if (nueva !== repetir) {
          showToast("Las contraseñas no coinciden.");
          return;
        }

        const result = await submitForm(endpoints.password, new FormData(form));

        if (result.success) {
          showToast(result.message || "Contraseña actualizada correctamente");
          form.reset();
          content.querySelectorAll("[data-eye]").forEach((btn) => {
            const input = btn.parentElement.querySelector("input");
            input.type = "password";
            btn.innerHTML = eyeClosed;
          });
        } else {
          showToast(result.message || "No se pudo cambiar la contraseña");
        }
      });
    }

    if (section === "logout") {
      handleLogout();
    }
  }

  function render(section) {
    content.innerHTML = sections[section] ? sections[section]() : sections.datos();
    document.querySelectorAll(".menu-item").forEach((m) =>
      m.classList.toggle("active", m.dataset.section === section)
    );
    bindSection(section);
    content.scrollTo({ top: 0, behavior: "smooth" });
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

  if (openTrigger) {
    openTrigger.addEventListener("click", (event) => {
      if (event.target.closest(".logout-link")) return;
      openModal();
    });
  }

  closeBtn.addEventListener("click", closeModal);

  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) closeModal();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && overlay.classList.contains("open")) closeModal();
  });

  menuConfig.forEach((item) => {
    const btn = document.querySelector(`.menu-item[data-section="${item.section}"]`);
    if (btn) btn.addEventListener("click", () => render(item.section));
  });
});