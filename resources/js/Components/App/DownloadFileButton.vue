<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { ArrowDownCircleIcon } from "@heroicons/vue/24/outline";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { httpGet } from "@/Helper/http-helper";

const props = defineProps({
    all: {
        type: Boolean,
        required: false,
        default: false,
    },
    ids: {
        type: Array,
        required: false,
    },
});

const page = usePage();

const download = () => {
    if (!props.all && !props.ids.length) {
        return;
    }

    const urlParams = new URLSearchParams();
    urlParams.append("parent_id", page.props.rootFolder.id);
    if (props.all) {
        urlParams.append("all", props.all ? 1 : 0);
    } else {
        for (let id of props.ids) {
            urlParams.append("ids[]", id);
        }
    }

    httpGet(`${route("files.download")}?${urlParams.toString()}`).then(
        (res) => {
            if (!res.url) return;

            const alink = document.createElement("a");
            alink.download = res.filename;
            alink.href = res.url;
            alink.click();
        }
    );
};
</script>

<template>
    <PrimaryButton @click="download">
        <ArrowDownCircleIcon class="w-4 h-4 mr-2" /> Download
    </PrimaryButton>
</template>
