<script setup lang="ts">
import { computed } from 'vue';
import { UserGroupIcon } from '@heroicons/vue/20/solid';
import Checkbox from '@/packages/ui/src/Input/Checkbox.vue';
import { Field, FieldDescription, FieldLabel } from '../field';

const model = defineModel<string[]>({
    default: () => [],
});

const props = withDefaults(
    defineProps<{
        members: { id: string; name: string }[];
        isPublic: boolean;
        loading?: boolean;
    }>(),
    {
        loading: false,
    }
);

const description = computed(() =>
    props.isPublic
        ? 'Everyone in the organization can see a public project. Selected members are still assigned to it.'
        : 'Only the selected members can see this project.'
);
</script>

<template>
    <Field>
        <FieldLabel id="project-members-label" :icon="UserGroupIcon">Members</FieldLabel>
        <div
            role="group"
            aria-labelledby="project-members-label"
            :aria-busy="loading"
            data-testid="project_access"
            class="max-h-48 overflow-y-auto rounded-md border border-input-border bg-input-background shadow-sm">
            <p v-if="loading" class="px-3 py-2.5 text-sm text-text-tertiary">Loading members...</p>
            <p v-else-if="members.length === 0" class="px-3 py-2.5 text-sm text-text-tertiary">
                No members to add.
            </p>
            <label
                v-for="member in members"
                :key="member.id"
                class="flex min-h-10 cursor-pointer items-center gap-2.5 px-3 text-sm text-text-primary hover:bg-card-background-active">
                <Checkbox v-model:checked="model" :value="member.id" />
                <span class="truncate">{{ member.name }}</span>
            </label>
        </div>
        <FieldDescription>{{ description }}</FieldDescription>
    </Field>
</template>
