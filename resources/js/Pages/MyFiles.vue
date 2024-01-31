<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const { files } = defineProps({
    files: Object,
});
</script>

<template>
    <AuthenticatedLayout>
        <table
            class="w-full text-sm text-left text-gray-500 rounded overflow-hidden shadow"
        >
            <thead
                class="text-xs text-gray-700 uppercase tracking-wider bg-gray-100 dark:bg-gray-700 dark:text-gray-200"
            >
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Owner</th>
                    <th class="px-6 py-3">Size</th>
                    <th class="px-6 py-3">Last Modifued</th>
                </tr>
            </thead>

            <tbody>
                <tr
                    class="bg-white border-b"
                    v-for="file in files.data"
                    :key="file.id"
                >
                    <td
                        class="px-6 py-4 font-medium tracking-wider text-gray-900 whitespace-nowrap"
                    >
                        {{ file.name }}
                    </td>
                    <td
                        class="px-6 py-4 font-medium tracking-wider text-gray-900 whitespace-nowrap"
                    >
                        {{ file.owner }}
                    </td>
                    <td
                        class="px-6 py-4 font-medium tracking-wider text-gray-900 whitespace-nowrap"
                    >
                        {{ file.size }}
                    </td>
                    <td
                        class="px-6 py-4 font-medium tracking-wider text-gray-900 whitespace-nowrap"
                    >
                        {{ file.updated_at }}
                    </td>
                </tr>
            </tbody>
        </table>
    </AuthenticatedLayout>
</template>
