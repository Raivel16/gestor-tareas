// Script.js - Funcionalidad del Tablero Kanban con soporte de archivos y notificaciones

let currentTaskId = null;
let currentColumn = null;
let editingTaskId = null;
let allTasks = [];
let currentImageFile = null;
let keepCurrentImage = true;

document.addEventListener("DOMContentLoaded", () => {
  setupEventListeners();
  loadTasks();
});

function setupEventListeners() {
  // Logout
  document.getElementById("logoutBtn").addEventListener("click", handleLogout);

  // Task Form
  const taskForm = document.getElementById("taskForm");
  if (taskForm) {
    taskForm.addEventListener("submit", handleTaskSubmit);
  }

  // Search
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    searchInput.addEventListener("input", handleSearch);
  }

  // Suggest Order Button
  const suggestOrderBtn = document.getElementById("suggestOrderBtn");
  if (suggestOrderBtn) {
    suggestOrderBtn.addEventListener("click", handleSuggestOrder);
  }

  // Drag and Drop
  setupDragAndDrop();
}

function setupDragAndDrop() {
  const containers = document.querySelectorAll(".tasks-container");

  containers.forEach((container) => {
    container.addEventListener("dragover", handleDragOver);
    container.addEventListener("drop", handleDrop);
    container.addEventListener("dragleave", handleDragLeave);
  });
}

function handleDragStart(e) {
  const taskCard = e.target.closest(".task-card");
  if (!taskCard) return;

  taskCard.classList.add("dragging");
  e.dataTransfer.effectAllowed = "move";
  e.dataTransfer.setData("taskId", taskCard.dataset.taskId);
  e.dataTransfer.setData("sourceColumn", taskCard.dataset.column);
}

function handleDragOver(e) {
  e.preventDefault();
  e.dataTransfer.dropEffect = "move";

  const container = e.currentTarget;
  const draggingCard = document.querySelector(".dragging");

  if (!draggingCard) return;

  // Obtener el elemento sobre el que estamos
  const afterElement = getDragAfterElement(container, e.clientY);

  if (afterElement == null) {
    container.appendChild(draggingCard);
  } else {
    container.insertBefore(draggingCard, afterElement);
  }

  container.classList.add("drag-over");
}

function handleDragLeave(e) {
  if (e.target === e.currentTarget) {
    e.currentTarget.classList.remove("drag-over");
  }
}

/**
 * Determina después de qué elemento debe insertarse la tarjeta arrastrada
 * Corrige el bug de no poder arrastrar del final al inicio
 */
function getDragAfterElement(container, y) {
  const draggableElements = [
    ...container.querySelectorAll(".task-card:not(.dragging)"),
  ];

  return draggableElements.reduce(
    (closest, child) => {
      const box = child.getBoundingClientRect();
      const offset = y - box.top - box.height / 2;

      if (offset < 0 && offset > closest.offset) {
        return { offset: offset, element: child };
      } else {
        return closest;
      }
    },
    { offset: Number.NEGATIVE_INFINITY }
  ).element;
}

async function handleDrop(e) {
  e.preventDefault();
  const container = e.currentTarget;
  container.classList.remove("drag-over");

  const taskId = parseInt(e.dataTransfer.getData("taskId"));
  const sourceColumn = e.dataTransfer.getData("sourceColumn");
  const newColumn = container.dataset.column;

  // Remover clase dragging
  const draggingCard = document.querySelector(".dragging");
  if (draggingCard) {
    draggingCard.classList.remove("dragging");
  }

  if (sourceColumn === newColumn) {
    // Reordenar dentro de la misma columna
    await updateColumnOrder(newColumn);
  } else {
    // Mover a otra columna
    await moveTaskToColumn(taskId, newColumn);
  }
}

async function moveTaskToColumn(taskId, newColumn) {
  try {
    showLoading("Moviendo tarea...");

    const response = await fetch("api/tasks.php?action=move", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id: taskId, column: newColumn }),
    });

    const result = await response.json();

    hideLoading();

    if (result.success) {
      await loadTasks();
      showToast("Tarea movida exitosamente", "success");
    } else {
      showToast(result.message || "Error al mover la tarea", "error");
      await loadTasks(); // Recargar para revertir cambios visuales
    }
  } catch (error) {
    hideLoading();
    console.error("Error:", error);
    showToast("Error de conexión al mover la tarea", "error");
    await loadTasks();
  }
}

async function updateColumnOrder(column) {
  const container = document.querySelector(
    `.tasks-container[data-column="${column}"]`
  );
  const taskCards = container.querySelectorAll(".task-card");
  const order = Array.from(taskCards).map((card) =>
    parseInt(card.dataset.taskId)
  );

  try {
    const response = await fetch("api/tasks.php?action=reorder", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ column, order }),
    });

    const result = await response.json();

    if (!result.success) {
      showToast(result.message || "Error al reordenar las tareas", "error");
      await loadTasks(); // Recargar para revertir cambios visuales
    }
  } catch (error) {
    console.error("Error:", error);
    showToast("Error de conexión al reordenar tareas", "error");
    await loadTasks();
  }
}

function openTaskModal(column) {
  currentColumn = column;
  editingTaskId = null;
  currentImageFile = null;
  keepCurrentImage = false;

  document.getElementById("modalTitle").textContent = "Nueva Tarea";
  document.getElementById("taskForm").reset();
  document.getElementById("imagePreview").innerHTML = "";

  const today = new Date().toISOString().split("T")[0];
  document.getElementById("taskDate").min = today;

  const modal = document.getElementById("taskModal");
  modal.classList.remove("hidden");
}

function closeTaskModal() {
  const modal = document.getElementById("taskModal");
  modal.classList.add("hidden");
  currentColumn = null;
  editingTaskId = null;
  currentImageFile = null;
  keepCurrentImage = false;
}

async function handleTaskSubmit(e) {
  e.preventDefault();

  const title = document.getElementById("taskTitle").value.trim();
  const description = document.getElementById("taskDescription").value.trim();
  const date = document.getElementById("taskDate").value;
  const priority = document.getElementById("taskPriority").value;
  const tag = document.getElementById("taskTag").value.trim();

  // Validación estricta
  if (!title || !description || !date || !priority || !tag) {
    showToast("Todos los campos son obligatorios (excepto imagen)", "warning");
    return;
  }

  const taskData = {
    title,
    description,
    date,
    priority,
    tag,
    column: currentColumn,
  };

  try {
    showLoading(editingTaskId ? "Actualizando tarea..." : "Creando tarea...");

    let response;

    // Si hay imagen nueva, usar FormData
    if (currentImageFile) {
      const formData = new FormData();
      formData.append("title", title);
      formData.append("description", description);
      formData.append("date", date);
      formData.append("priority", priority);
      formData.append("tag", tag);
      formData.append("column", currentColumn);
      formData.append("image", currentImageFile);

      if (editingTaskId) {
        formData.append("id", editingTaskId);
        formData.append("keep_image", "false");
        response = await fetch("api/tasks.php?action=update", {
          method: "POST",
          body: formData,
        });
      } else {
        response = await fetch("api/tasks.php?action=create", {
          method: "POST",
          body: formData,
        });
      }
    } else {
      // Sin imagen nueva, usar JSON
      if (editingTaskId) {
        taskData.id = editingTaskId;
        taskData.keep_image = keepCurrentImage;
        response = await fetch("api/tasks.php?action=update", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(taskData),
        });
      } else {
        response = await fetch("api/tasks.php?action=create", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(taskData),
        });
      }
    }

    const result = await response.json();

    hideLoading();

    if (result.success) {
      closeTaskModal();
      await loadTasks();
      showToast(
        editingTaskId ? "Tarea actualizada" : "Tarea creada",
        "success"
      );
    } else {
      showToast(result.message || "Error al guardar la tarea", "error");
    }
  } catch (error) {
    hideLoading();
    console.error("Error:", error);
    showToast("Error de conexión al guardar la tarea", "error");
  }
}

function previewImage(event) {
  const file = event.target.files[0];
  const preview = document.getElementById("imagePreview");

  if (file) {
    // Validación de tamaño (5MB)
    if (file.size > 5 * 1024 * 1024) {
      showToast("La imagen no debe superar 5MB", "error");
      event.target.value = "";
      return;
    }

    // Validación de tipo
    const validTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
    if (!validTypes.includes(file.type)) {
      showToast("Solo se permiten archivos JPG, PNG, GIF o WEBP", "error");
      event.target.value = "";
      return;
    }

    currentImageFile = file;
    keepCurrentImage = false;

    const reader = new FileReader();
    reader.onload = function (e) {
      preview.innerHTML = `
        <img src="${e.target.result}" alt="Preview">
        <button type="button" class="remove-image" onclick="removeImage()">✕</button>
      `;
    };
    reader.readAsDataURL(file);
  } else {
    currentImageFile = null;
    preview.innerHTML = "";
  }
}

function removeImage() {
  document.getElementById("taskImage").value = "";
  document.getElementById("imagePreview").innerHTML = "";
  currentImageFile = null;
  keepCurrentImage = false;
}

function renderTasks() {
  const columns = ["todo", "inprogress", "done"];

  columns.forEach((column) => {
    const container = document.querySelector(
      `.tasks-container[data-column="${column}"]`
    );
    const tasks = allTasks.filter((task) => task.columna === column);

    container.innerHTML = "";

    tasks.forEach((task) => {
      const taskCard = createTaskCard(task);
      container.appendChild(taskCard);
    });

    // Actualizar contador
    const countElement = document.querySelector(
      `.task-count[data-column="${column}"]`
    );
    if (countElement) {
      countElement.textContent = tasks.length;
    }
  });
}

function createTaskCard(task) {
  const card = document.createElement("div");
  card.className = "task-card";
  card.draggable = true;
  card.dataset.taskId = task.id;
  card.dataset.column = task.columna;

  card.addEventListener("dragstart", handleDragStart);
  card.addEventListener("click", () => openTaskDetailModal(task.id));

  const priorityColors = {
    high: "#ef4444",
    medium: "#f59e0b",
    low: "#10b981",
  };

  const priorityLabels = {
    high: "Alta",
    medium: "Media",
    low: "Baja",
  };

  let imageHtml = "";
  if (task.imagen_url) {
    imageHtml = `<img src="${task.imagen_url}" alt="Task image" class="task-image">`;
  }

  card.innerHTML = `
    ${imageHtml}
    <div class="task-header">
      <span class="task-priority" style="background-color: ${
        priorityColors[task.prioridad]
      }">
        ${priorityLabels[task.prioridad]}
      </span>
    </div>
    <h4 class="task-title">${escapeHtml(task.titulo)}</h4>
    ${
      task.descripcion
        ? `<p class="task-description">${escapeHtml(task.descripcion)}</p>`
        : ""
    }
    <div class="task-meta">
      ${
        task.fecha_limite
          ? `<span>📅 ${formatDate(task.fecha_limite)}</span>`
          : ""
      }
      ${
        task.curso
          ? `<span class="task-tag">🎓 ${escapeHtml(task.curso)}</span>`
          : ""
      }
    </div>
  `;

  return card;
}

function openTaskDetailModal(taskId) {
  const task = allTasks.find((t) => t.id === taskId);
  if (!task) return;

  currentTaskId = taskId;

  const priorityLabels = {
    high: "Alta",
    medium: "Media",
    low: "Baja",
  };

  let imageHtml = "";
  if (task.imagen_url) {
    imageHtml = `
      <div class="detail-image">
        <img src="${task.imagen_url}" alt="Task image">
      </div>
    `;
  }

  const detailContent = document.getElementById("taskDetailContent");
  detailContent.innerHTML = `
    ${imageHtml}
    <div class="detail-field">
      <label>Título:</label>
      <p>${escapeHtml(task.titulo)}</p>
    </div>
    ${
      task.descripcion
        ? `
      <div class="detail-field">
        <label>Descripción:</label>
        <p>${escapeHtml(task.descripcion)}</p>
      </div>
    `
        : ""
    }
    <div class="detail-field">
      <label>Prioridad:</label>
      <p>${priorityLabels[task.prioridad]}</p>
    </div>
    ${
      task.fecha_limite
        ? `
      <div class="detail-field">
        <label>Fecha Límite:</label>
        <p>${formatDate(task.fecha_limite)}</p>
      </div>
    `
        : ""
    }
    ${
      task.curso
        ? `
      <div class="detail-field">
        <label>Curso:</label>
        <p>${escapeHtml(task.curso)}</p>
      </div>
    `
        : ""
    }
    <div class="detail-actions">
      <button class="btn btn-primary" onclick="editTask(${taskId})">Editar</button>
    </div>
  `;

  const modal = document.getElementById("taskDetailModal");
  modal.classList.remove("hidden");
}

function closeTaskDetailModal() {
  const modal = document.getElementById("taskDetailModal");
  modal.classList.add("hidden");
  currentTaskId = null;
}

function editTask(taskId) {
  const task = allTasks.find((t) => t.id === taskId);
  if (!task) return;

  closeTaskDetailModal();

  editingTaskId = taskId;
  currentColumn = task.columna;
  currentImageFile = null;
  keepCurrentImage = true;

  document.getElementById("modalTitle").textContent = "Editar Tarea";
  document.getElementById("taskTitle").value = task.titulo;
  document.getElementById("taskDescription").value = task.descripcion || "";
  document.getElementById("taskDate").value = task.fecha_limite || "";
  document.getElementById("taskPriority").value = task.prioridad;
  document.getElementById("taskTag").value = task.curso || "";

  // Mostrar imagen existente
  const preview = document.getElementById("imagePreview");
  if (task.imagen_url) {
    preview.innerHTML = `
      <img src="${task.imagen_url}" alt="Current image">
      <button type="button" class="remove-image" onclick="removeImage()">✕</button>
    `;
  } else {
    preview.innerHTML = "";
  }

  const modal = document.getElementById("taskModal");
  modal.classList.remove("hidden");
}

function deleteCurrentTask() {
  if (!currentTaskId) return;

  showConfirm(
    "Eliminar Tarea",
    "¿Estás seguro de que deseas eliminar esta tarea? Esta acción no se puede deshacer.",
    async () => {
      try {
        showLoading("Eliminando tarea...");

        const response = await fetch("api/tasks.php?action=delete", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ id: currentTaskId }),
        });

        const result = await response.json();

        hideLoading();

        if (result.success) {
          closeTaskDetailModal();
          await loadTasks();
          showToast("Tarea eliminada exitosamente", "success");
        } else {
          showToast(result.message || "Error al eliminar la tarea", "error");
        }
      } catch (error) {
        hideLoading();
        console.error("Error:", error);
        showToast("Error de conexión al eliminar la tarea", "error");
      }
    }
  );
}

async function loadTasks() {
  try {
    const response = await fetch("api/tasks.php?action=list");
    const result = await response.json();

    if (result.success) {
      allTasks = result.data || [];
      renderTasks();
    } else {
      showToast("Error al cargar las tareas", "error");
    }
  } catch (error) {
    console.error("Error:", error);
    showToast("Error de conexión al cargar las tareas", "error");
  }
}

function handleSearch(e) {
  const searchTerm = e.target.value.toLowerCase();

  const taskCards = document.querySelectorAll(".task-card");
  taskCards.forEach((card) => {
    const title = card.querySelector(".task-title").textContent.toLowerCase();
    const description = card.querySelector(".task-description");
    const descText = description ? description.textContent.toLowerCase() : "";

    if (title.includes(searchTerm) || descText.includes(searchTerm)) {
      card.style.display = "";
    } else {
      card.style.display = "none";
    }
  });
}

async function handleSuggestOrder() {
  showConfirm(
    "Sugerir Orden con IA",
    "¿Deseas que la IA analice tus tareas de 'Hacer' y sugiera un orden óptimo?",
    async () => {
      try {
        showLoading("La IA está analizando tus tareas...");

        const response = await fetch("api/tasks.php?action=suggest_order", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
        });

        const result = await response.json();

        hideLoading();

        if (result.success) {
          // Mostrar explicación y botón para aplicar
          showAIExplanation(
            result.data.explanation,
            result.data.count,
            result.data.order
          );
        } else {
          showToast(result.message || "Error al obtener sugerencia", "error");
        }
      } catch (error) {
        hideLoading();
        console.error("Error:", error);
        showToast("Error de conexión con IA", "error");
      }
    }
  );
}

async function applySuggestedOrder(order) {
  try {
    showLoading("Aplicando nuevo orden...");

    const response = await fetch("api/tasks.php?action=reorder", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ column: "todo", order: order }),
    });

    const result = await response.json();
    hideLoading();

    if (result.success) {
      document.getElementById("aiModal").classList.add("hidden");
      await loadTasks();
      showToast("Orden aplicado exitosamente", "success");
    } else {
      showToast(result.message || "Error al aplicar orden", "error");
    }
  } catch (error) {
    hideLoading();
    console.error("Error:", error);
    showToast("Error al aplicar orden", "error");
  }
}

async function handleLogout() {
  showConfirm(
    "Cerrar Sesión",
    "¿Estás seguro de que deseas cerrar sesión?",
    async () => {
      try {
        showLoading("Cerrando sesión...");

        const response = await fetch("api/auth.php?action=logout", {
          method: "POST",
        });

        hideLoading();

        window.location.href = "login.php";
      } catch (error) {
        hideLoading();
        console.error("Error:", error);
        window.location.href = "login.php";
      }
    }
  );
}

// Utility Functions
function escapeHtml(unsafe) {
  if (!unsafe) return "";
  return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function formatDate(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString);
  const options = { year: "numeric", month: "short", day: "numeric" };
  return date.toLocaleDateString("es-ES", options);
}
