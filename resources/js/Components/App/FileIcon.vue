<script setup>
import {
    AttachFileImg,
    AudioImg,
    ExcelImg,
    Image,
    PDFImg,
    TextFileImg,
    VideoImg,
    WordImg,
    ZipImg,
} from "@/Images";
import { FolderIcon } from "@heroicons/vue/24/outline";
import {
    isImage,
    isAudio,
    isVideo,
    isPDF,
    isText,
    isZip,
    isWord,
    isExcel,
} from "@/Helper/file-helper.js";

const { file } = defineProps({
    file: Object,
});
</script>

<template>
    <span class="w-6 h-6 flex items-center justify-center mr-2">
        <FolderIcon class="w-6 h-6" v-if="file.is_folder" />

        <img
            v-else-if="isImage(file)"
            :src="Image"
            alt="Image Icon"
            class="max-w-full"
        />
        <img
            v-else-if="isPDF(file)"
            :src="PDFImg"
            alt="PDF Icon"
            class="max-w-full"
        />
        <img
            v-else-if="isAudio(file)"
            :src="AudioImg"
            alt="Audio Icon"
            class="max-w-full"
        />
        <img
            v-else-if="isVideo(file)"
            :src="VideoImg"
            alt="Video Icon"
            class="max-w-full"
        />
        <img
            v-else-if="isWord(file)"
            :src="WordImg"
            alt="Word Icon"
            class="max-w-full"
        />
        <img
            v-else-if="isExcel(file)"
            :src="ExcelImg"
            alt="Excel Icon"
            class="max-w-full"
        />
        <img
            v-else-if="isZip(file)"
            :src="ZipImg"
            alt="Zip Icon"
            class="max-w-full"
        />
        <img
            v-else-if="isText(file)"
            :src="TextFileImg"
            alt="Text File Icon"
            class="max-w-full"
        />
        <img
            v-else
            :src="AttachFileImg"
            alt="Attach File Icon"
            class="max-w-full"
        />
    </span>
</template>
