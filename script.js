// Script.js - Funcionalidad del Tablero Kanban

let currentUser = null;
let currentTaskId = null;
let currentColumn = null;
let editingTaskId = null;
let allTasks = [];

document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
    setupEventListeners();
    loadTasks();
    renderTasks();
});

function initializeApp() {
    const user = localStorage.getItem('currentUser');
    if (!user) {
        window.location.href = 'login.html';
        return;
    }

    currentUser = JSON.parse(user);
    console.log('[v0] Usuario autenticado:', currentUser);
}

function setupEventListeners() {
    // Logout
    document.getElementById('logoutBtn').addEventListener('click', handleLogout);

    // Task Form
    const taskForm = document.getElementById('taskForm');
    if (taskForm) {
        taskForm.addEventListener('submit', handleTaskSubmit);
    }

    // Search
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }

    // Drag and Drop
    setupDragAndDrop();
}

function setupDragAndDrop() {
    const containers = document.querySelectorAll('.tasks-container');

    containers.forEach(container => {
        container.addEventListener('dragover', handleDragOver);
        container.addEventListener('drop', handleDrop);
        container.addEventListener('dragleave', handleDragLeave);
    });
}

function handleDragStart(e) {
    const taskCard = e.target.closest('.task-card');
    if (!taskCard) return;

    taskCard.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('taskId', taskCard.dataset.taskId);
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    this.classList.add('drag-over');
}

function handleDragLeave(e) {
    if (e.target === this) {
        this.classList.remove('drag-over');
    }
}

function handleDrop(e) {
    e.preventDefault();
    this.classList.remove('drag-over');

    const taskId = e.dataTransfer.getData('taskId');
    const newColumn = this.dataset.column;

    const task = allTasks.find(t => t.id === taskId);
    if (task) {
        task.column = newColumn;
        saveTasks();
        renderTasks();
    }

    // Remover clase dragging
    document.querySelectorAll('.task-card.dragging').forEach(card => {
        card.classList.remove('dragging');
    });
}

function openTaskModal(column) {
    currentColumn = column;
    editingTaskId = null;
    currentTaskId = null;

    document.getElementById('modalTitle').textContent = 'Nueva Tarea';
    document.getElementById('taskForm').reset();
    document.getElementById('imagePreview').innerHTML = '';

    document.getElementById('taskModal').classList.remove('hidden');
}

function closeTaskModal() {
    document.getElementById('taskModal').classList.add('hidden');
    currentColumn = null;
    editingTaskId = null;
}

function closeTaskDetailModal() {
    document.getElementById('taskDetailModal').classList.add('hidden');
    currentTaskId = null;
}

function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('imagePreview');

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(file);
    }
}

function handleTaskSubmit(e) {
    e.preventDefault();

    const title = document.getElementById('taskTitle').value.trim();
    const description = document.getElementById('taskDescription').value.trim();
    const date = document.getElementById('taskDate').value;
    const priority = document.getElementById('taskPriority').value;
    const tag = document.getElementById('taskTag').value.trim();
    const imageFile = document.getElementById('taskImage').files[0];

    if (!title) {
        alert('El título es requerido');
        return;
    }

    const task = {
        id: editingTaskId || generateId(),
        title,
        description,
        date,
        priority,
        tag,
        image: null,
        column: currentColumn,
        createdAt: editingTaskId ? 
            allTasks.find(t => t.id === editingTaskId).createdAt :
            new Date().toISOString()
    };

    if (imageFile) {
        const reader = new FileReader();
        reader.onload = (e) => {
            task.image = e.target.result;
            saveTask(task);
            closeTaskModal();
        };
        reader.readAsDataURL(imageFile);
    } else {
        saveTask(task);
        closeTaskModal();
    }
}

function saveTask(task) {
    if (editingTaskId) {
        const index = allTasks.findIndex(t => t.id === editingTaskId);
        if (index !== -1) {
            allTasks[index] = task;
        }
    } else {
        allTasks.push(task);
    }
    saveTasks();
    renderTasks();
}

function editTask(taskId) {

    closeTaskDetailModal();

    const task = allTasks.find(t => t.id === taskId);
    if (!task) return;

    editingTaskId = taskId;
    currentColumn = task.column;
    currentTaskId = null;

    document.getElementById('modalTitle').textContent = 'Editar Tarea';
    document.getElementById('taskTitle').value = task.title;
    document.getElementById('taskDescription').value = task.description;
    document.getElementById('taskDate').value = task.date;
    document.getElementById('taskPriority').value = task.priority;
    document.getElementById('taskTag').value = task.tag;

    if (task.image) {
        document.getElementById('imagePreview').innerHTML = `<img src="${task.image}" alt="Task">`;
    }

    document.getElementById('taskModal').classList.remove('hidden');



}

function viewTaskDetail(taskId) {
    const task = allTasks.find(t => t.id === taskId);
    if (!task) return;

    currentTaskId = taskId;

    let detailHTML = `
        <div class="detail-section">
            <div class="detail-label">Título</div>
            <div class="detail-value">${escapeHtml(task.title)}</div>
        </div>
    `;

    if (task.description) {
        detailHTML += `
            <div class="detail-section">
                <div class="detail-label">Descripción</div>
                <div class="detail-value">${escapeHtml(task.description)}</div>
            </div>
        `;
    }

    if (task.image) {
        detailHTML += `<img src="${task.image}" alt="Task" class="detail-image">`;
    }

    if (task.date) {
        detailHTML += `
            <div class="detail-section">
                <div class="detail-label">Fecha</div>
                <div class="detail-value">${formatDate(task.date)}</div>
            </div>
        `;
    }

    detailHTML += `
        <div class="detail-section">
            <div class="detail-label">Prioridad</div>
            <div class="detail-value">
                <span class="task-priority ${task.priority}">
                    ${getPriorityLabel(task.priority)}
                </span>
            </div>
        </div>
    `;

    if (task.tag) {
        detailHTML += `
            <div class="detail-section">
                <div class="detail-label">Categoría</div>
                <div class="detail-value"><span class="task-tag">${escapeHtml(task.tag)}</span></div>
            </div>
        `;
    }

    detailHTML += `
        <div class="detail-section">
            <div class="detail-label">Creada el</div>
            <div class="detail-value">${formatDate(task.createdAt)}</div>
        </div>
        <div class="modal-actions" style="justify-content: space-between; border-top: 1px solid var(--border-color); padding-top: 16px;">
            <button type="button" class="btn btn-secondary" onclick="editTask('${taskId}')">Editar</button>
        </div>
    `;

    document.getElementById('taskDetailContent').innerHTML = detailHTML;
    document.getElementById('taskDetailModal').classList.remove('hidden');
}

function deleteCurrentTask() {
    if (!currentTaskId) return;

    if (confirm('¿Estás seguro de que deseas eliminar esta tarea?')) {
        allTasks = allTasks.filter(t => t.id !== currentTaskId);
        saveTasks();
        closeTaskDetailModal();
        renderTasks();
    }
}

function renderTasks() {
    const containers = document.querySelectorAll('.tasks-container');

    containers.forEach(container => {
        const column = container.dataset.column;
        container.innerHTML = '';

        const tasks = allTasks.filter(t => t.column === column);

        tasks.forEach(task => {
            const taskCard = createTaskCard(task);
            container.appendChild(taskCard);
        });

        // Actualizar contador
        const counter = document.querySelector(`[data-column="${column}"]`).parentElement.querySelector('.task-count');
        if (counter) {
            counter.textContent = tasks.length;
        }
    });

    setupDragAndDrop();
}

function createTaskCard(task) {
    const card = document.createElement('div');
    card.className = 'task-card';
    card.dataset.taskId = task.id;
    card.draggable = true;

    let cardHTML = `<div class="task-title" onclick="viewTaskDetail('${task.id}')">${escapeHtml(task.title)}</div>`;

    if (task.description) {
        cardHTML += `<div class="task-description">${escapeHtml(task.description)}</div>`;
    }

    if (task.image) {
        cardHTML += `<img src="${task.image}" alt="Task" class="task-image" onclick="viewTaskDetail('${task.id}')">`;
    }

    const metaItems = [];

    if (task.date) {
        metaItems.push(`<div class="task-date">📅 ${formatDate(task.date)}</div>`);
    }

    metaItems.push(`<span class="task-priority ${task.priority}">${getPriorityLabel(task.priority)}</span>`);

    if (task.tag) {
        metaItems.push(`<span class="task-tag">${escapeHtml(task.tag)}</span>`);
    }

    if (metaItems.length > 0) {
        cardHTML += `<div class="task-meta">${metaItems.join('')}</div>`;
    }

    card.innerHTML = cardHTML;

    card.addEventListener('dragstart', handleDragStart);
    card.addEventListener('dragend', () => card.classList.remove('dragging'));

    return card;
}

function handleSearch(e) {
    const query = e.target.value.toLowerCase();

    if (query === '') {
        renderTasks();
        return;
    }

    const containers = document.querySelectorAll('.tasks-container');
    containers.forEach(container => {
        const cards = container.querySelectorAll('.task-card');
        cards.forEach(card => {
            const title = card.querySelector('.task-title').textContent.toLowerCase();
            const description = card.querySelector('.task-description')?.textContent.toLowerCase() || '';

            if (title.includes(query) || description.includes(query)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
}

function handleLogout() {
    if (confirm('¿Deseas cerrar sesión?')) {
        localStorage.removeItem('currentUser');
        window.location.href = 'login.html';
    }
}

function loadTasks() {
    const saved = localStorage.getItem(`tasks_${currentUser.email}`);
    if (saved) {
        allTasks = JSON.parse(saved);
    }
}

function saveTasks() {
    localStorage.setItem(`tasks_${currentUser.email}`, JSON.stringify(allTasks));
}

function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        day: 'numeric',
        month: 'short'
    });
}

function getPriorityLabel(priority) {
    const labels = {
        high: 'Alta',
        medium: 'Media',
        low: 'Baja'
    };
    return labels[priority] || priority;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
