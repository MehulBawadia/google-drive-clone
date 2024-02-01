<script setup>
import { ref, onMounted } from "vue";
import Navigation from "@/Components/App/Navigation.vue";
import SearchForm from "@/Components/App/SearchForm.vue";
import UserSettingsDropDown from "@/Components/App/UserSettingsDropDown.vue";
import { FILE_UPLOAD_STARTED, emitter } from "@/event-bus";
import { useForm, usePage } from "@inertiajs/vue3";
import FormProgress from "@/Components/App/FormProgress.vue";

const page = usePage();
const dragOver = ref(false);

onMounted(() => {
    emitter.on(FILE_UPLOAD_STARTED, uploadFiles);
});

const fileUploadForm = useForm({
    files: [],
    relative_paths: [],
    parent_id: null,
});

const onDrop = (e) => {
    dragOver.value = false;
    const files = e.dataTransfer.files;
    if (!files.length) {
        return;
    }

    uploadFiles(files);
};
const onDragOver = () => {
    dragOver.value = true;
};
const onDragLeave = () => {
    dragOver.value = false;
};

const uploadFiles = (files) => {
    fileUploadForm.parent_id = page.props.rootFolder.id;
    fileUploadForm.files = files;
    fileUploadForm.relative_paths = [...files].map(
        (file) => file.webkitRelativePath
    );

    fileUploadForm.post(route("files.store"));
};
</script>

<template>
    <div class="h-screen bg-gray-50 flex w-full gap-4">
        <Navigation />

        <main
            class="flex flex-col flex-1 px-4 overflow-hidden"
            :class="dragOver ? 'dropzone' : ''"
            @drop.prevent="onDrop"
            @dragover.prevent="onDragOver"
            @dragleave.prevent="onDragLeave"
        >
            <template
                v-if="dragOver"
                class="text-gray-500 text-sm text-center py-8"
            >
                Drag files here to upload.
            </template>

            <template v-else>
                <div class="flex items-center justify-between w-full">
                    <SearchForm />
                    <UserSettingsDropDown />
                </div>

                <div class="flex flex-col flex-1 overflow-hidden">
                    <slot></slot>
                </div>
            </template>
        </main>
    </div>

    <FormProgress :form="fileUploadForm" />
</template>

<style scoped>
.dropzone {
    width: 100%;
    height: 100%;
    color: #8d8d8d;
    border: 2px dashed gray;
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>
