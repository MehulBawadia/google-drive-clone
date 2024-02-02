<script setup>
import { ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { TrashIcon } from "@heroicons/vue/24/outline";
import ConfirmationDialog from "@/Components/App/ConfirmationDialog.vue";
import { showErrorDialog } from "@/event-bus";

const props = defineProps({
    deleteAll: {
        type: Boolean,
        required: false,
        default: false,
    },
    deleteIds: {
        type: Array,
        required: false,
    },
});

const showConfirmDialog = ref(false);

const emit = defineEmits(["delete"]);

const page = usePage();
const deleteFileForm = useForm({
    all: null,
    ids: [],
    parent_id: null,
});

const onDeleteClick = () => {
    if (!props.deleteAll && !props.deleteIds.length) {
        showErrorDialog("Please select at least one file or folder to delete.");
        return;
    }

    showConfirmDialog.value = true;
};

const closeConfirmDialog = () => {
    showConfirmDialog.value = false;
};

const onDeleteConfirm = () => {
    console.log("Process the deleting of files...");
    deleteFileForm.parent_id = page.props.rootFolder.id;
    if (props.deleteAll) {
        deleteFileForm.all = true;
    } else {
        deleteFileForm.ids = props.deleteIds;
    }

    deleteFileForm.delete(route("files.destroy"), {
        onSuccess: () => {
            showConfirmDialog.value = false;
            emit("delete");
        },
    });
};
</script>

<template>
    <button
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
        @click="onDeleteClick"
    >
        <TrashIcon class="w-4 h-4 mr-2" /> Delete
    </button>

    <ConfirmationDialog
        message="Are you sure you want to delete selected files? This method cannot be undone."
        :show="showConfirmDialog"
        @cancel="closeConfirmDialog"
        @confirm="onDeleteConfirm"
    />
</template>
