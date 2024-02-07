<script setup>
import { router, useForm } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import TextInput from "@/Components/TextInput.vue";
import { ON_SEARCH, emitter } from "@/event-bus";

const searchInput = ref("");
let params = "";

const onSearch = () => {
    params.set("search", searchInput.value);
    router.get(`${window.location.pathname}?${params.toString()}`);
};

onMounted(() => {
    params = new URLSearchParams(window.location.search);
    searchInput.value = params.get("search") ?? "";
    emitter.emit(ON_SEARCH, searchInput.value);
});
</script>

<template>
    <div class="w-[600px] h-[80px] flex items-center">
        <TextInput
            type="text"
            class="block w-full mr-2"
            v-model="searchInput"
            placeholder="Search for files and folder"
            @keyup.enter.prevent="onSearch"
            autocomplete
        />
    </div>
</template>
