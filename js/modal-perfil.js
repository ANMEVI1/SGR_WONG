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
  };

  function render(section) {
    content.innerHTML = sections[section]();
    menuItems.forEach((m) => m.classList.toggle("active", m.dataset.section === section));
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

  openBtn.addEventListener("click", openModal);
  closeBtn.addEventListener("click", closeModal);
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) closeModal();
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && overlay.classList.contains("open")) closeModal();
  });
  menuItems.forEach((item) => {
    item.addEventListener("click", () => render(item.dataset.section));
  });
});
