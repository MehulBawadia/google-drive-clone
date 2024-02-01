<script setup>
import { FILE_UPLOAD_STARTED, emitter } from "@/event-bus";
import { MenuItem } from "@headlessui/vue";

const onChange = (e) => {
    emitter.emit(FILE_UPLOAD_STARTED, e.target.files);
};
</script>

<template>
    <MenuItem v-slot="{ active }">
        <a href="#" class="text-gray-700 block px-4 py-2 text-sm relative">
            Upload Files
            <input
                type="file"
                class="absolute left-0 top-0 bottom-0 right-0 cursor-pointer opacity-0"
                multiple
                @change="onChange"
            />
        </a>
    </MenuItem>
</template>
