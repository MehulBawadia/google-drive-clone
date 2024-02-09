<script setup>
import { computed, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { ShareIcon } from "@heroicons/vue/24/outline";
import { showErrorDialog, showSuccessNotification } from "@/event-bus";
import ShareFilesModal from "./ShareFilesModal.vue";

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

const showEmailsModal = ref(false);

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
        showErrorDialog("Please select at least one file or folder to share.");
        return;
    }

    showEmailsModal.value = true;
};

const closeConfirmDialog = () => {
    showEmailsModal.value = false;
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
            showEmailsModal.value = false;
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
        :disabled="btnDisabled"
        :class="btnDisabled ? 'disabled:opacity-50 cursor-not-allowed' : ''"
        @click="onClick"
    >
        <ShareIcon class="w-4 h-4 mr-2" /> Share
    </button>

    <ShareFilesModal
        v-model="showEmailsModal"
        :all-selected="allSelected"
        :selected-ids="selectedIds"
    />
</template>
