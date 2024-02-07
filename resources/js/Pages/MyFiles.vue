<script setup>
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    ChevronRightIcon,
    HomeIcon,
    StarIcon as StarSolidIcon,
} from "@heroicons/vue/20/solid";
import FileIcon from "@/Components/App/FileIcon.vue";
import { ref, onMounted, onUpdated, computed } from "vue";
import { httpPost } from "@/Helper/http-helper";
import Checkbox from "@/Components/Checkbox.vue";
import DeleteFileButton from "@/Components/App/DeleteFileButton.vue";
import DownloadFileButton from "@/Components/App/DownloadFileButton.vue";
import { StarIcon as StarOutlineIcon } from "@heroicons/vue/24/outline";
import { ON_SEARCH, emitter, showSuccessNotification } from "@/event-bus";
import ShareFileButton from "@/Components/App/ShareFileButton.vue";

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
const onlyFavourites = ref(false);

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

    httpPost(allFiles.value.next).then((res) => {
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

const toggleFavourite = (file) => {
    let actionType = "favourited";
    if (file.is_favourite) {
        actionType = "unfavourited";
    }

    httpPost(route("files.toggleFavourite"), { id: file.id }).then(() => {
        file.is_favourite = !file.is_favourite;
        showSuccessNotification(
            `The file has been successfully ${actionType}.`
        );
    });
};

const showOnlyFavourites = () => {
    const favourites = usePage().props.favourites;

    if (favourites === true) {
        return router.get(route("myFiles"));
    }

    return router.get(route("myFiles"), { favourites: 1 });
};

onUpdated(() => {
    allFiles.value = {
        data: props.files.data,
        next: props.files.links.next,
    };
});

const loadMoreIntersect = ref(null);
const page = usePage();
let search = ref("");
onMounted(() => {
    const favourites = page.props.favourites;
    onlyFavourites.value = favourites === true;
    search.value = page.props.search ?? "";
    emitter.on(ON_SEARCH, (value) => {
        search.value = value;
    });

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
        <Head title="My Files" />

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

            <div class="flex items-center">
                <label class="flex items-center mr-3">
                    <Checkbox
                        v-model:checked="onlyFavourites"
                        @change="showOnlyFavourites"
                        class="mr-2"
                    />
                    Only Favorites
                </label>

                <ShareFileButton
                    :all-selected="allSelected"
                    :selected-ids="selectedIds"
                />

                <DownloadFileButton
                    :all="allSelected"
                    :ids="selectedIds"
                    class="mr-2"
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
                        <th class=""></th>
                        <th class="pl-6 pr-0 py-3 w-7 max-w-7">Name</th>
                        <th class="px-6 py-3" v-if="search">Path</th>
                        <th class="px-6 py-3">Owner</th>
                        <th class="px-6 py-3">Size</th>
                        <th class="px-6 py-3">Last Modified</th>
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
                        @dblclick="openFolder(file)"
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
                            class="py-4 font-medium tracking-wider text-gray-900 whitespace-nowrap"
                        >
                            <div
                                class="flex items-center"
                                @click.stop.prevent="toggleFavourite(file)"
                            >
                                <StarOutlineIcon
                                    v-if="!file.is_favourite"
                                    class="w-4 h-4"
                                />
                                <StarSolidIcon
                                    v-else
                                    class="w-4 h-4 text-yellow-500"
                                />
                            </div>
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
                            v-if="search"
                            class="px-6 py-4 font-medium tracking-wider text-gray-900 whitespace-nowrap"
                        >
                            {{ file.path }}
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
                v-if="!allFiles.data.length"
                class="text-center tracking-wide py-3 text-gray-700 bg-white shadow rounded-b"
            >
                No files or folders available in this directory.
            </div>

            <div ref="loadMoreIntersect"></div>
        </div>
    </AuthenticatedLayout>
</template>
