<script setup lang="ts">
import { computed } from 'vue';
import MultiselectDropdown from '@/packages/ui/src/Input/MultiselectDropdown.vue';
import Checkbox from '@/packages/ui/src/Input/Checkbox.vue';
import { useMembersQuery } from '@/utils/useMembersQuery';
import type { Member } from '@/packages/api/src';

const { members: allMembers } = useMembersQuery();

const showInactive = defineModel<boolean>('showInactive', { default: false });

const members = computed(() =>
    showInactive.value
        ? allMembers.value
        : allMembers.value.filter((member) => !member.is_placeholder)
);

function getKeyFromItem(item: Member) {
    return item.id;
}

function getNameForItem(item: Member) {
    return item.name;
}

const emit = defineEmits<{
    submit: [];
}>();
</script>

<template>
    <MultiselectDropdown
        search-placeholder="Search for a Member..."
        :items="members"
        :get-key-from-item="getKeyFromItem"
        :get-name-for-item="getNameForItem"
        @submit="emit('submit')">
        <template #trigger>
            <slot name="trigger"></slot>
        </template>
        <template #content-before-list>
            <label
                class="mt-2 flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-text-secondary hover:bg-card-background-active cursor-pointer">
                <Checkbox v-model:checked="showInactive" />
                <span>Show inactive</span>
            </label>
        </template>
    </MultiselectDropdown>
</template>
