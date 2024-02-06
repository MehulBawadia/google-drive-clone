<script setup>
import { nextTick, ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import Modal from "@/Components/App/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "../PrimaryButton.vue";
import { showSuccessNotification } from "@/event-bus";

const props = defineProps({
    modelValue: Boolean,
    allSelected: Boolean,
    selectedIds: Array,
});
const emit = defineEmits(["update:modelValue"]);
const page = usePage();

const emailInput = ref(null);

const form = useForm({
    email: "",
    all: false,
    ids: [],
    parent_id: null,
});

const share = () => {
    form.parent_id = page.props.rootFolder.id;
    if (props.allSelected) {
        form.all = true;
        form.ids = [];
    } else {
        form.all = false;
        form.ids = props.selectedIds;
    }
    form.post(route("files.share"), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessNotification(
                `Selected file(s) will be shared with ${form.email} if it exists in the application.`
            );
            closeModal();
        },
        onError: () => {
            emailInput.value.focus();
        },
    });
};

const onShow = () => {
    nextTick(() => {
        emailInput.value.focus();
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset();

    emit("update:modelValue");
};
</script>

<template>
    <Modal :show="props.modelValue" @show="onShow" max-width="sm">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">Share Files</h2>

            <div class="mt-6">
                <InputLabel for="email" value="Email Address" class="sr-only" />
                <TextInput
                    type="text"
                    id="email"
                    class="block w-full mt-1"
                    placeholder="Enter email address"
                    v-model="form.email"
                    @keyup.enter="share"
                    :class="
                        form?.errors?.email
                            ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                            : ''
                    "
                    ref="emailInput"
                />
                <InputError :message="form?.errors?.email" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="share"
                    >Submit</PrimaryButton
                >
            </div>
        </div>
    </Modal>
</template>
