import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import ProjectAccessSelect from './ProjectAccessSelect.vue';

const members = [
    { id: 'member-a', name: 'Ada Lovelace' },
    { id: 'member-b', name: 'Grace Hopper' },
];

describe('ProjectAccessSelect', () => {
    it('lets you pick more than one member', async () => {
        const wrapper = mount(ProjectAccessSelect, {
            props: {
                members,
                isPublic: false,
                modelValue: [] as string[],
            },
        });

        const checkboxes = wrapper.findAll('input[type="checkbox"]');
        const first = checkboxes[0];
        const second = checkboxes[1];
        if (!first || !second) {
            throw new Error('Expected a checkbox for each member');
        }
        await first.setValue(true);
        await second.setValue(true);

        const updates = wrapper.emitted('update:modelValue') ?? [];
        const last = updates.at(-1)?.[0] as string[];
        expect(last).toEqual(['member-a', 'member-b']);
        expect(wrapper.text()).toContain('Only the selected members can see this project.');
    });

    it('explains that a public project stays visible to the organization', () => {
        const wrapper = mount(ProjectAccessSelect, {
            props: {
                members,
                isPublic: true,
                modelValue: ['member-a'],
            },
        });

        expect(wrapper.text()).toContain(
            'Everyone in the organization can see a public project. Selected members are still assigned to it.'
        );
        expect((wrapper.get('input[value="member-a"]').element as HTMLInputElement).checked).toBe(
            true
        );
        expect((wrapper.get('input[value="member-b"]').element as HTMLInputElement).checked).toBe(
            false
        );
    });

    it('shows an empty state when there is nobody to add', () => {
        const wrapper = mount(ProjectAccessSelect, {
            props: {
                members: [],
                isPublic: false,
                modelValue: [],
            },
        });

        expect(wrapper.get('[data-testid="project_access"]').text()).toContain(
            'No members to add.'
        );
    });
});
