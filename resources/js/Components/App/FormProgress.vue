<script setup>
const props = defineProps({
    form: Object,
});
</script>

<template>
    <div
        v-if="form.progress"
        class="absolute w-[240px] flex flex-col bg-white rounded shadow-lg border border-gray-800 right-8 bottom-4 p-2"
    >
        <div class="text-xs mb-2">Uploading {{ form.files.length }} files</div>
        <div class="h-3 bg-gray-100 rounded-md">
            <div
                class="h-full bg-gray-900 rounded-md transition-all"
                :style="{ width: form.progress.percentage + '%' }"
            ></div>
        </div>
    </div>
</template>
