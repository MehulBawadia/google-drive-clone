<script setup>
import Navigation from "@/Components/App/Navigation.vue";
import SearchForm from "@/Components/App/SearchForm.vue";
import UserSettingsDropDown from "@/Components/App/UserSettingsDropDown.vue";
</script>

<template>
    <div class="h-screen bg-gray-50 flex w-full gap-4">
        <Navigation />

        <main class="flex flex-col flex-1 px-4 overflow-hidden">
            <div class="flex items-center justify-between w-full">
                <SearchForm />
                <UserSettingsDropDown />
            </div>

            <div class="flex flex-col flex-1 overflow-hidden">
                <slot></slot>
            </div>
        </main>
    </div>
</template>
