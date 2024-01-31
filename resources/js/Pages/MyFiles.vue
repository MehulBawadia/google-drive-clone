<script setup>
import { Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ChevronRightIcon, HomeIcon } from "@heroicons/vue/20/solid";

const { files } = defineProps({
    files: Object,
    folder: Object,
    ancestors: Array,
});

const openFolder = (file) => {
    if (!file.is_folder) {
        return;
    }

    router.visit(route("myFiles", { folder: file.path }));
};
</script>

<template>
    <AuthenticatedLayout>
        <nav class="flex items-center justify-between p-1 mb-3">
            <ol class="inline-flex items-center space-x-1">
                <li
                    v-for="ancestor in ancestors.data"
                    :key="ancestor.id"
                    class="inline-flex items-center"
                >
                    <Link
                        v-if="!ancestor.parent_id"
                        :href="route('myFiles')"
                        class="flex items-center text-sm font-medium text-gray-700 hover:text-blue-600"
                    >
                        <HomeIcon class="w-4 h-4 mr-1" />
                        My Files
                    </Link>

                    <div v-else class="flex items-center">
                        <ChevronRightIcon class="w-5 h-5" />
                        <Link
                            :href="route('myFiles', { folder: ancestor.path })"
                            class="text-sm font-medium text-gray-700 hover:text-blue-600"
                        >
                            {{ ancestor.name }}
                        </Link>
                    </div>
                </li>
            </ol>
        </nav>

        <table
            class="w-full text-sm text-left text-gray-500 rounded overflow-hidden shadow"
        >
            <thead
                class="text-xs text-gray-700 uppercase tracking-wider bg-gray-200"
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
                    class="bg-white border-b hover:bg-gray-100 cursor-pointer transition ease-in-out duration-200"
                    v-for="file in files.data"
                    :key="file.id"
                    @dblclick="openFolder(file)"
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

        <div
            v-if="!files.data.length"
            class="text-center tracking-wide py-3 text-gray-700 bg-white shadow rounded-b"
        >
            No files or folders available in this directory.
        </div>
    </AuthenticatedLayout>
</template>
