/**
 * Notification System - Modales y Toasts profesionales
 * Sistema de notificaciones moderno para reemplazar alert()
 */

// Crear contenedores si no existen
function createNotificationContainers() {
  if (!document.getElementById("notification-container")) {
    const container = document.createElement("div");
    container.id = "notification-container";
    document.body.appendChild(container);
  }

  if (!document.getElementById("toast-container")) {
    const container = document.createElement("div");
    container.id = "toast-container";
    document.body.appendChild(container);
  }

  if (!document.getElementById("loading-overlay")) {
    const overlay = document.createElement("div");
    overlay.id = "loading-overlay";
    overlay.className = "loading-overlay hidden";
    overlay.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p class="loading-message">Procesando...</p>
            </div>
        `;
    document.body.appendChild(overlay);
  }
}

// Inicializar al cargar
document.addEventListener("DOMContentLoaded", createNotificationContainers);

/**
 * Muestra un modal de notificación
 * @param {string} title - Título del modal
 * @param {string} message - Mensaje del modal
 * @param {string} type - Tipo: 'success', 'error', 'warning', 'info'
 * @param {function} onConfirm - Callback al confirmar (opcional)
 */
function showModal(title, message, type = "info", onConfirm = null) {
  const container = document.getElementById("notification-container");

  const icons = {
    success: "✓",
    error: "✕",
    warning: "⚠",
    info: "ℹ",
  };

  const modal = document.createElement("div");
  modal.className = "notification-modal";
  modal.innerHTML = `
        <div class="notification-overlay"></div>
        <div class="notification-content ${type}">
            <div class="notification-icon">${icons[type] || icons.info}</div>
            <h3 class="notification-title">${title}</h3>
            <p class="notification-message">${message}</p>
            <div class="notification-actions">
                <button class="btn btn-primary notification-btn">Aceptar</button>
            </div>
        </div>
    `;

  container.appendChild(modal);

  // Animar entrada
  setTimeout(() => modal.classList.add("show"), 10);

  // Cerrar al hacer clic
  const closeModal = () => {
    modal.classList.remove("show");
    setTimeout(() => modal.remove(), 300);
    if (onConfirm) onConfirm();
  };

  modal
    .querySelector(".notification-btn")
    .addEventListener("click", closeModal);
  modal
    .querySelector(".notification-overlay")
    .addEventListener("click", closeModal);
}

/**
 * Muestra un modal de confirmación
 * @param {string} title - Título
 * @param {string} message - Mensaje
 * @param {function} onConfirm - Callback al confirmar
 * @param {function} onCancel - Callback al cancelar
 */
function showConfirm(title, message, onConfirm, onCancel = null) {
  const container = document.getElementById("notification-container");

  const modal = document.createElement("div");
  modal.className = "notification-modal";
  modal.innerHTML = `
        <div class="notification-overlay"></div>
        <div class="notification-content warning">
            <div class="notification-icon">?</div>
            <h3 class="notification-title">${title}</h3>
            <p class="notification-message">${message}</p>
            <div class="notification-actions">
                <button class="btn btn-secondary notification-cancel">Cancelar</button>
                <button class="btn btn-primary notification-confirm">Confirmar</button>
            </div>
        </div>
    `;

  container.appendChild(modal);
  setTimeout(() => modal.classList.add("show"), 10);

  const closeModal = (confirmed) => {
    modal.classList.remove("show");
    setTimeout(() => modal.remove(), 300);
    if (confirmed && onConfirm) {
      onConfirm();
    } else if (!confirmed && onCancel) {
      onCancel();
    }
  };

  modal
    .querySelector(".notification-confirm")
    .addEventListener("click", () => closeModal(true));
  modal
    .querySelector(".notification-cancel")
    .addEventListener("click", () => closeModal(false));
  modal
    .querySelector(".notification-overlay")
    .addEventListener("click", () => closeModal(false));
}

/**
 * Muestra un toast (notificación temporal)
 * @param {string} message - Mensaje
 * @param {string} type - Tipo: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Duración en ms (default 3000)
 */
function showToast(message, type = "info", duration = 3000) {
  const container = document.getElementById("toast-container");

  const icons = {
    success: "✓",
    error: "✕",
    warning: "⚠",
    info: "ℹ",
  };

  const toast = document.createElement("div");
  toast.className = `toast ${type}`;
  toast.innerHTML = `
        <div class="toast-icon">${icons[type] || icons.info}</div>
        <div class="toast-message">${message}</div>
    `;

  container.appendChild(toast);

  // Animar entrada
  setTimeout(() => toast.classList.add("show"), 10);

  // Auto-remover
  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => toast.remove(), 300);
  }, duration);

  // Cerrar al hacer clic
  toast.addEventListener("click", () => {
    toast.classList.remove("show");
    setTimeout(() => toast.remove(), 300);
  });
}

/**
 * Muestra overlay de carga
 * @param {string} message - Mensaje de carga
 */
function showLoading(message = "Procesando...") {
  const overlay = document.getElementById("loading-overlay");
  overlay.querySelector(".loading-message").textContent = message;
  overlay.classList.remove("hidden");
}

/**
 * Oculta overlay de carga
 */
function hideLoading() {
  const overlay = document.getElementById("loading-overlay");
  overlay.classList.add("hidden");
}

/**
 * Muestra modal con explicación de IA
 * @param {string} explanation - Explicación de la IA
 * @param {number} taskCount - Número de tareas ordenadas
 * @param {array} order - Array de IDs ordenados (opcional)
 */
function showAIExplanation(explanation, taskCount, order = null) {
  const container = document.getElementById("notification-container");

  const modal = document.createElement("div");
  modal.className = "notification-modal";

  let actionButtons = `
    <button class="btn btn-primary notification-btn">Entendido</button>
  `;

  if (order) {
    actionButtons = `
        <button class="btn btn-secondary notification-cancel">Cancelar</button>
        <button class="btn btn-primary notification-apply">Reordenar automáticamente</button>
    `;
  }

  modal.innerHTML = `
        <div class="notification-overlay"></div>
        <div class="notification-content success ai-explanation-modal">
            <div class="notification-icon">🤖</div>
            <h3 class="notification-title">Sugerencia de IA</h3>
            <p class="notification-subtitle">${taskCount} tareas analizadas</p>
            <div class="ai-explanation">
                <p>${explanation}</p>
            </div>
            <div class="notification-actions">
                ${actionButtons}
            </div>
        </div>
    `;

  container.appendChild(modal);
  setTimeout(() => modal.classList.add("show"), 10);

  const closeModal = () => {
    modal.classList.remove("show");
    setTimeout(() => modal.remove(), 300);
  };

  if (order) {
    modal.querySelector(".notification-apply").addEventListener("click", () => {
      closeModal();
      if (typeof applySuggestedOrder === "function") {
        applySuggestedOrder(order);
      }
    });
    modal
      .querySelector(".notification-cancel")
      .addEventListener("click", closeModal);
  } else {
    modal
      .querySelector(".notification-btn")
      .addEventListener("click", closeModal);
  }

  modal
    .querySelector(".notification-overlay")
    .addEventListener("click", closeModal);
}
