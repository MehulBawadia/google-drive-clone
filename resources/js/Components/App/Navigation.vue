<script setup>
import { Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import CreateNewDropdown from "@/Components/App/CreateNewDropdown.vue";
import NavLink from "@/Components/App/NavLink.vue";
</script>

<template>
    <nav class="min-w-[200px]">
        <div class="h-[80px] px-3 flex items-center gap-3">
            <Link href="/">
                <ApplicationLogo
                    class="block h-9 w-auto fill-current text-gray-800"
                />
            </Link>
            GDStore
        </div>

        <div class="px-3">
            <CreateNewDropdown />

            <div class="py-3">
                <NavLink href="#" :active="true">My Files</NavLink>
                <NavLink href="#">Shared With Me</NavLink>
                <NavLink href="#">Shared By Me</NavLink>
                <NavLink href="#">Trash</NavLink>
            </div>
        </div>
    </nav>
</template>
