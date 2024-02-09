<script setup>
import { Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import CreateNewDropdown from "@/Components/App/CreateNewDropdown.vue";
import NavLink from "@/Components/App/NavLink.vue";
</script>

<template>
    <nav class="min-w-[200px]">
        <Link :href="route('myFiles')" class="h-[80px] px-3 flex items-center">
            <ApplicationLogo class="h-7" />
            <span class="mx-0.5 text-2xl font-bold text-blue-600">
                GDStore
            </span>
            <ApplicationLogo class="h-7" />
        </Link>

        <div class="px-3">
            <CreateNewDropdown />

            <div class="py-3">
                <NavLink
                    :href="route('myFiles')"
                    :active="$page.props.current_route === 'myFiles'"
                    >My Files</NavLink
                >
                <NavLink
                    :href="route('sharedWithMe')"
                    :active="$page.props.current_route === 'sharedWithMe'"
                    >Shared With Me</NavLink
                >
                <NavLink
                    :href="route('sharedByMe')"
                    :active="$page.props.current_route === 'sharedByMe'"
                    >Shared By Me</NavLink
                >
                <NavLink
                    :href="route('trash')"
                    :active="$page.props.current_route === 'trash'"
                    >Trash</NavLink
                >
            </div>
        </div>
    </nav>
</template>
