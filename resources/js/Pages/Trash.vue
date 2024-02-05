<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ChevronRightIcon, HomeIcon } from "@heroicons/vue/20/solid";
import FileIcon from "@/Components/App/FileIcon.vue";
import { ref, onMounted, onUpdated, computed } from "vue";
import { httpGet } from "@/Helper/http-helper";
import Checkbox from "@/Components/Checkbox.vue";
import DeleteFileButton from "@/Components/App/DeleteFileButton.vue";
import RestoreFileButton from "@/Components/App/RestoreFileButton.vue";

const props = defineProps({
    files: Object,
    folder: Object,
    ancestors: Object,
});

const allFiles = ref({
    data: props.files.data,
    next: props.files.links.next,
});

const allSelected = ref(false);
const selected = ref({});

const selectedIds = computed(() => {
    return Object.entries(selected.value)
        .filter((elem) => elem[1])
        .map((elem) => elem[0]);
});

const openFolder = (file) => {
    if (!file.is_folder) {
        return;
    }

    router.visit(route("myFiles", { folder: file.path }));
};

const loadMore = () => {
    if (allFiles.value.next === null) {
        return;
    }

    httpGet(allFiles.value.next).then((res) => {
        allFiles.value.data = [...allFiles.value.data, ...res.data];
        allFiles.value.next = res.links.next;
    });
};

const onSelectAllChange = () => {
    allFiles.value.data.forEach((f) => {
        selected.value[f.id] = allSelected.value;
    });
};

const toggleFileSelect = (file) => {
    selected.value[file.id] = !selected.value[file.id];
    onSelectCheckboxChange(file);
};

const onSelectCheckboxChange = (file) => {
    if (!selected.value[file.id]) {
        allSelected.value = false;
    } else {
        let checked = true;

        for (let file of allFiles.value.data) {
            if (!selected.value[file.id]) {
                checked = false;
                break;
            }
        }

        allSelected.value = checked;
    }
};

const onDelete = () => {
    allSelected.value = false;
    selected.value = {};
};

onUpdated(() => {
    allFiles.value = {
        data: props.files.data,
        next: props.files.links.next,
    };
});

const loadMoreIntersect = ref(null);
onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => entry.isIntersecting && loadMore());
        },
        {
            rootMargin: "-250px 0px 0px 0px",
        }
    );

    observer.observe(loadMoreIntersect.value);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Trash" />

        <nav class="flex items-center justify-end p-1 mb-3">
            <div class="space-x-4">
                <RestoreFileButton
                    :all-selected="allSelected"
                    :selected-ids="selectedIds"
                />

                <DeleteFileButton
                    :delete-all="allSelected"
                    :delete-ids="selectedIds"
                    @delete="onDelete"
                />
            </div>
        </nav>

        <div class="flex-1 overflow-auto">
            <table
                class="w-full text-sm text-left text-gray-500 rounded overflow-hidden shadow"
            >
                <thead
                    class="text-xs text-gray-700 uppercase tracking-wider bg-gray-200"
                >
                    <tr>
                        <th class="px-6 py-3">
                            <Checkbox
                                v-model:checked="allSelected"
                                @change="onSelectAllChange"
                            />
                        </th>
                        <th class="pl-6 pr-0 py-3 w-7 max-w-7">Name</th>
                        <th class="px-6 py-3">Path</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        class="border-b hover:bg-blue-100 cursor-pointer transition ease-in-out duration-200"
                        :class="
                            selected[file.id] || allSelected
                                ? 'bg-blue-50'
                                : 'bg-white'
                        "
                        v-for="file in allFiles.data"
                        :key="file.id"
                        @click="($event) => toggleFileSelect(file)"
                    >
                        <td
                            class="pl-6 py-4 pr-0 w-7 max-w-7 font-medium tracking-wider text-gray-900 whitespace-nowrap"
                        >
                            <Checkbox
                                v-model="selected[file.id]"
                                :checked="selected[file.id] || allSelected"
                                @change="
                                    ($event) => onSelectCheckboxChange(file)
                                "
                            />
                        </td>
                        <td
                            class="px-6 py-4 font-medium tracking-wider text-gray-900 whitespace-nowrap"
                        >
                            <div class="flex items-center">
                                <FileIcon :file="file" />
                                {{ file.name }}
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 font-medium tracking-wider text-gray-900 whitespace-nowrap"
                        >
                            {{ file.path }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                v-if="!allFiles.data.length"
                class="text-center tracking-wide py-3 text-gray-700 bg-white shadow rounded-b"
            >
                No files or folders available in this directory.
            </div>

            <div ref="loadMoreIntersect"></div>
        </div>
    </AuthenticatedLayout>
</template>
