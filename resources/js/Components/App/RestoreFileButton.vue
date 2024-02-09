<script setup>
import { computed, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { ArrowLeftStartOnRectangleIcon } from "@heroicons/vue/24/outline";
import ConfirmationDialog from "@/Components/App/ConfirmationDialog.vue";
import { showErrorDialog, showSuccessNotification } from "@/event-bus";

const props = defineProps({
    allSelected: {
        type: Boolean,
        required: false,
        default: false,
    },
    selectedIds: {
        type: Array,
        required: false,
    },
});

const showConfirmDialog = ref(false);

const emit = defineEmits(["restore"]);

const form = useForm({
    all: null,
    ids: [],
    parent_id: null,
});

const btnDisabled = computed(() => {
    return !props.allSelected && !props.selectedIds.length;
});

const onClick = () => {
    if (!props.allSelected && !props.selectedIds.length) {
        showErrorDialog(
            "Please select at least one file or folder to restore."
        );
        return;
    }

    showConfirmDialog.value = true;
};

const closeConfirmDialog = () => {
    showConfirmDialog.value = false;
};

const onConfirm = () => {
    if (props.allSelected) {
        form.all = true;
        form.ids = [];
    } else {
        form.ids = props.selectedIds;
    }

    form.post(route("files.restore"), {
        onSuccess: () => {
            showConfirmDialog.value = false;
            showSuccessNotification(
                "Selected files have been successfully restored."
            );
            emit("restore");
        },
    });
};
</script>

<template>
    <button
        class="mr-2 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
        @click="onClick"
        :disabled="btnDisabled"
        :class="btnDisabled ? 'disabled:opacity-50 cursor-not-allowed' : ''"
    >
        <ArrowLeftStartOnRectangleIcon class="w-4 h-4 mr-2" /> Restore
    </button>

    <ConfirmationDialog
        message="Are you sure you want to restore selected files?"
        :show="showConfirmDialog"
        @cancel="closeConfirmDialog"
        @confirm="onConfirm"
    />
</template>
