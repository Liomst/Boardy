<script setup>
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import draggable from 'vuedraggable'
import 'bootstrap/dist/css/bootstrap.min.css'
import * as bootstrap from 'bootstrap'

const props = defineProps({
    todosByStatus: Object,
    statuses: Array,
})

const lists = reactive({})
const activeTodo = ref(null)
const modalMode = ref(null) // 'create' or 'edit'

// Initialize lists for each status
for (const status of props.statuses) {
    lists[status] = props.todosByStatus[status] ? [...props.todosByStatus[status]] : []
}

// Update the order after drag & drop
function updateOrder() {
    const todosByStatusUpdate = {}
    for (const status of props.statuses) {
        todosByStatusUpdate[status] = lists[status].map(item => item.id)
    }

    router.put('/todos/reorder', { todosByStatus: todosByStatusUpdate }, {
        preserveScroll: true,
    })
}

// Open create modal and initialize empty todo
function openCreateModal(status = 'backlog') {
    activeTodo.value = { title: '', description: '', due_date: '', status }
    modalMode.value = 'create'
    new bootstrap.Modal(document.getElementById('createTodoModal')).show()
}

// Open edit modal with existing todo data
function openEditModal(todo) {
    activeTodo.value = { ...todo }
    modalMode.value = 'edit'
    new bootstrap.Modal(document.getElementById('editTodoModal')).show()
}

// Save a new todo or update an existing one
async function saveTodo() {
    if (modalMode.value === 'create') {
        try {
            await router.post('/todos', {
                title: activeTodo.value.title,
                description: activeTodo.value.description,
                due_date: activeTodo.value.due_date,
                status: activeTodo.value.status,
            }, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    // Add new todo to the local list
                    const createdTodo = page.props.todosByStatus[activeTodo.value.status].slice(-1)[0]
                    lists[activeTodo.value.status].push(createdTodo)
                }
            })
            bootstrap.Modal.getInstance(document.getElementById('createTodoModal')).hide()
        } catch (error) {
            console.error('Error creating todo:', error)
        }
    } else if (modalMode.value === 'edit') {
        try {
            await router.put(`/todos/${activeTodo.value.id}`, {
                title: activeTodo.value.title,
                description: activeTodo.value.description,
                due_date: activeTodo.value.due_date,
                status: activeTodo.value.status,
            }, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    // Update the local list with edited todo
                    for (const status of props.statuses) {
                        const index = lists[status].findIndex(item => item.id === activeTodo.value.id)
                        if (index !== -1) {
                            lists[status][index] = { ...activeTodo.value }
                            break
                        }
                    }
                }
            })
            bootstrap.Modal.getInstance(document.getElementById('editTodoModal')).hide()
        } catch (error) {
            console.error('Error updating todo:', error)
        }
    }
}

// Format date string as DD-MM-YY
function formatDate(dateString) {
    if (!dateString) return ''
    const date = new Date(dateString)
    const day = String(date.getDate()).padStart(2, '0')
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const year = String(date.getFullYear()).slice(-2)
    return `${day}-${month}-${year}`
}
</script>

<template>
    <!-- Create task button -->
    <div class="px-6 pt-6">
        <button class="btn btn-primary" @click="openCreateModal()">
            <i class="bi bi-plus-lg me-1"></i> Add New Task
        </button>
    </div>

    <!-- Task board with columns per status -->
    <div class="d-flex gap-4 p-6 items-start w-100">
        <div
            v-for="status in statuses"
            :key="status"
            class="flex-shrink-0 card-container bg-gray-100 rounded p-4"
        >
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold capitalize">{{ status }}</h2>
            </div>

            <draggable
                v-model="lists[status]"
                group="todos"
                @end="updateOrder"
                item-key="id"
                :animation="200"
                :ghost-class="'dragging-ghost'"
                class="min-h-[100px]"
            >
                <template #item="{ element }">
                    <div
                        class="mb-2 p-3 bg-white rounded shadow cursor-pointer"
                        @click="openEditModal(element)"
                    >
                        <strong>{{ element.title }}</strong>
                        <div class="text-sm text-gray-600 truncate">{{ element.description }}</div>
                        <div class="text-xs text-gray-400 mt-1">
                            Due: {{ formatDate(element.due_date) }}
                        </div>
                    </div>
                </template>

                <template #footer>
                    <div
                        v-if="lists[status].length === 0"
                        class="p-6 bg-gray-200 text-center text-gray-500 border-2 border-dashed rounded"
                    >
                        Drop a task here
                    </div>
                </template>
            </draggable>
        </div>
    </div>

    <!-- Create Task Modal -->
    <div class="modal fade" id="createTodoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" v-if="activeTodo && modalMode === 'create'">
                <div class="modal-header">
                    <h5 class="modal-title">Create Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" v-model="activeTodo.title" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="4" v-model="activeTodo.description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" v-model="activeTodo.due_date" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" @click="saveTodo">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div class="modal fade" id="editTodoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" v-if="activeTodo && modalMode === 'edit'">
                <div class="modal-header">
                    <div>
                        <input type="text" class="form-control" v-model="activeTodo.title" />
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body d-flex justify-content-between">
                    <div class="mb-3 flex-grow-1 px-2">
                        <textarea class="form-control" rows="4" v-model="activeTodo.description"></textarea>
                    </div>
                    <div class="mb-3 flex-grow-1 px-2">
                        <input type="date" class="form-control" v-model="activeTodo.due_date" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" @click="saveTodo">Save</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.dragging-ghost {
    opacity: 0.7;
}

.cursor-pointer {
    cursor: pointer;
}

.form-control {
    border: 1px solid rgba(222, 226, 230, 0.4);
}

.card-container {
    min-width: 20rem;
}
</style>
