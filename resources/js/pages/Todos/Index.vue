<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import draggable from 'vuedraggable'

const props = defineProps({
    todosByStatus: Object,
    statuses: Array,
})

// Create a reactive copy to enable drag & drop functionality
const lists = reactive({ ...props.todosByStatus })

function updateOrder() {
    // Convert updated list data to the format expected by the backend
    const todosByStatusUpdate = {}
    for (const status of props.statuses) {
        todosByStatusUpdate[status] = lists[status].map(item => item.id)
    }

    router.put('/todos/reorder', { todosByStatus: todosByStatusUpdate }, {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="flex gap-4 p-6 overflow-x-auto max-w-full">
        <div
            v-for="status in statuses"
            :key="status"
            class="flex-shrink-0 w-64 bg-gray-100 rounded p-4"
        >
            <h2 class="font-bold mb-4 capitalize">
                {{ status.replace(/([A-Z])/g, ' $1') }}
            </h2>
            <draggable
                v-model="lists[status]"
                group="todos"
                @end="updateOrder"
                item-key="id"
            >
                <template #item="{ element }">
                    <div class="mb-2 p-3 bg-white rounded shadow cursor-move">
                        {{ element.title }}
                    </div>
                </template>
            </draggable>
        </div>
    </div>
</template>

<style>
.flex {
    scrollbar-width: thin;
    scrollbar-color: #a0aec0 transparent;
}

.flex::-webkit-scrollbar {
    height: 8px;
}

.flex::-webkit-scrollbar-thumb {
    background-color: #a0aec0;
    border-radius: 4px;
}
</style>
