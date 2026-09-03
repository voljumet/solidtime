<script setup lang="ts">
import { computed, ref } from 'vue';
import MultiselectDropdown from '@/packages/ui/src/Input/MultiselectDropdown.vue';
import Checkbox from '@/packages/ui/src/Input/Checkbox.vue';
import type { Client } from '@/packages/api/src';
import { useClientsQuery } from '@/utils/useClientsQuery';

const { clients: allClients } = useClientsQuery();

const showArchived = ref(false);

const clients = computed(() =>
    showArchived.value ? allClients.value : allClients.value.filter((client) => !client.is_archived)
);

function getKeyFromItem(item: Client) {
    return item.id;
}

function getNameForItem(item: Client) {
    return item.name;
}

const emit = defineEmits<{
    submit: [];
}>();
</script>

<template>
    <MultiselectDropdown
        search-placeholder="Search for a Client..."
        :items="clients"
        :get-key-from-item="getKeyFromItem"
        :get-name-for-item="getNameForItem"
        no-item-label="No Client"
        @submit="emit('submit')">
        <template #trigger>
            <slot name="trigger"></slot>
        </template>
        <template #content-before-list>
            <label
                class="mt-2 flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-text-secondary hover:bg-card-background-active cursor-pointer">
                <Checkbox v-model:checked="showArchived" />
                <span>Show archived</span>
            </label>
        </template>
    </MultiselectDropdown>
</template>
