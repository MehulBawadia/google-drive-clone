<script setup>
import { nextTick, ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import Modal from "@/Components/App/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "../PrimaryButton.vue";

const { modelValue } = defineProps({
    modelValue: Boolean,
});
const emit = defineEmits(["update:modelValue"]);

const folderNameInput = ref(null);

const form = useForm({
    name: "",
    parent_id: usePage().props.rootFolder?.id ?? null,
});

const createFolder = () => {
    form.post(route("folder.create"), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: () => {
            folderNameInput.value.focus();
        },
    });
};

const onShow = () => {
    nextTick(() => {
        folderNameInput.value.focus();
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset();

    emit("update:modelValue");
};
</script>

<template>
    <Modal :show="modelValue" @show="onShow" max-width="sm">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">Create New Folder</h2>

            <div class="mt-6">
                <InputLabel
                    for="folderName"
                    value="Folder Name"
                    class="sr-only"
                />
                <TextInput
                    type="text"
                    id="folderName"
                    class="block w-full mt-1"
                    placeholder="Folder Name"
                    v-model="form.name"
                    @keyup.enter="createFolder"
                    :class="
                        form?.errors?.name
                            ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                            : ''
                    "
                    ref="folderNameInput"
                />
                <InputError :message="form?.errors?.name" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="createFolder"
                    >Submit</PrimaryButton
                >
            </div>
        </div>
    </Modal>
</template>
