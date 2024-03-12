<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100"
    >
        <div>
            <Link href="/" class="flex items-center">
                <ApplicationLogo class="h-16" />
                <span class="mx-0.5 text-4xl font-bold text-blue-600">
                    GDStore
                </span>
                <ApplicationLogo class="h-16" />
            </Link>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"
        >
            <slot />
        </div>

        <slot name="registerLink"></slot>
    </div>
</template>
