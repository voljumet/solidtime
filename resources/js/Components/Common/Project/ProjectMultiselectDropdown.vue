<script setup lang="ts">
import { computed, ref } from 'vue';
import MultiselectDropdown from '@/packages/ui/src/Input/MultiselectDropdown.vue';
import Checkbox from '@/packages/ui/src/Input/Checkbox.vue';
import { useProjectsQuery } from '@/utils/useProjectsQuery';
import type { Project } from '@/packages/api/src';

// ponytail: filter by client_id here instead of a new "filtered projects" query, list is already in memory
const props = defineProps<{
    clientIds?: string[];
}>();

const { projects: allProjects } = useProjectsQuery();

const showArchived = ref(false);

const projects = computed(() => {
    let items = showArchived.value
        ? allProjects.value
        : allProjects.value.filter((project) => !project.is_archived);
    if (props.clientIds?.length) {
        items = items.filter((project) => props.clientIds!.includes(project.client_id ?? ''));
    }
    return items;
});

function getKeyFromItem(item: Project) {
    return item.id;
}

function getNameForItem(item: Project) {
    return item.name;
}

const emit = defineEmits<{
    submit: [];
}>();
</script>

<template>
    <MultiselectDropdown
        search-placeholder="Search for a Project..."
        :items="projects"
        :get-key-from-item="getKeyFromItem"
        :get-name-for-item="getNameForItem"
        no-item-label="No Project"
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
