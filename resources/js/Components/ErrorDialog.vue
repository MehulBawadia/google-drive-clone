<script setup>
import { ref, onMounted } from "vue";
import Modal from "@/Components/App/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { SHOW_ERROR_DIALOG, emitter } from "@/event-bus";

const show = ref(false);
const message = ref("");

const emit = defineEmits(["close"]);

const close = () => {
    show.value = false;
    message.value = "";
};

onMounted(() => {
    emitter.on(SHOW_ERROR_DIALOG, ({ message: msg }) => {
        show.value = true;
        message.value = msg;
    });
});
</script>

<template>
    <Modal :show="show" max-width="md">
        <div class="p-6">
            <h2 class="text-2xl mb-2 text-red-600 font-semibold">Error!</h2>

            <p>{{ message }}</p>

            <div class="mt-6 flex justify-end">
                <PrimaryButton @click="close">Okay</PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
