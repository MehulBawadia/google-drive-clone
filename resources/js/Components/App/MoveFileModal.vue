<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>
            
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                                Move {{ file ? file.name : 'file' }}
                            </h3>
                            
                            <!-- Breadcrumb navigation -->
                            <div class="flex items-center mb-3 text-sm text-gray-600">
                                <span class="mr-2">Location:</span>
                                <button 
                                    @click="navigateToRoot"
                                    class="text-blue-600 hover:text-blue-800 underline"
                                >
                                    My Files
                                </button>
                                <template v-for="(ancestor, index) in currentPath" :key="ancestor.id">
                                    <svg class="w-3 h-3 mx-1 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                    <button 
                                        @click="navigateToFolder(ancestor)"
                                        class="text-blue-600 hover:text-blue-800 underline"
                                    >
                                        {{ ancestor.name }}
                                    </button>
                                </template>
                            </div>
                            
                            <div class="max-h-64 overflow-y-auto border rounded-md">
                                <!-- Back button -->
                                <div 
                                    v-if="currentFolderId"
                                    @click="navigateBack"
                                    class="flex items-center justify-start p-3 cursor-pointer hover:bg-gray-100 transition-colors duration-200 border-b"
                                >
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                </div>

                                <!-- Current folder option -->
                                <div 
                                    v-if="!isFileInCurrentFolder"
                                    @click="selectCurrentFolder"
                                    :class="[
                                        'flex items-center p-3 cursor-pointer hover:bg-green-100 transition-colors duration-200',
                                        isCurrentFolderSelected ? 'bg-green-50 border-l-4 border-green-500' : 'border-l-4 border-transparent'
                                    ]"
                                >
                                    <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                    </svg>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            Select this folder {{ currentFolderName }}
                                        </div>
                                        <div class="text-xs text-gray-500">Move here</div>
                                    </div>
                                </div>
                                
                                <!-- Subfolders -->
                                <div 
                                    v-for="folder in availableFolders" 
                                    :key="folder.id"
                                    @dblclick="navigateToFolder(folder)"
                                    @click="selectFolder(folder)"
                                    :class="[
                                        'flex items-center p-3 cursor-pointer hover:bg-blue-100 transition-colors duration-200',
                                        selectedFolder?.id === folder.id ? 'bg-blue-50 border-l-4 border-blue-500' : 'border-l-4 border-transparent'
                                    ]"
                                >
                                    <svg class="w-5 h-5 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900">{{ folder.name }}</div>
                                        <div class="text-xs text-gray-500">Double-click to enter</div>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                                
                                <div v-if="!availableFolders.length && !currentFolderId" class="p-4 text-center text-gray-500">
                                    No folders available
                                </div>
                                
                                <div v-if="!availableFolders.length && currentFolderId" class="p-4 text-center text-gray-500">
                                    No subfolders in this directory
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button
                        @click="confirmMove"
                        :disabled="!selectedFolder && !isCurrentFolderSelected || isMoving"
                        :class="[
                            'inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto',
                            (!selectedFolder && !isCurrentFolderSelected || isMoving) 
                                ? 'bg-gray-400 cursor-not-allowed' 
                                : 'bg-blue-600 hover:bg-blue-500'
                        ]"
                    >
                        {{ isMoving ? 'Moving...' : 'Move' }}
                    </button>
                    <button
                        @click="closeModal"
                        :disabled="isMoving"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { httpGet, httpPost } from '@/Helper/http-helper';
import { showSuccessNotification, showErrorNotification } from '@/event-bus';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    file: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'moved']);

const availableFolders = ref([]);
const selectedFolder = ref(null);
const isMoving = ref(false);
const currentFolderId = ref(null);
const currentPath = ref([]);
const isCurrentFolderSelected = ref(false);

const currentFolderName = computed(() => {
    if (!currentFolderId.value) return '(Root)';
    return currentPath.value.length > 0 ? `(${currentPath.value[currentPath.value.length - 1].name})` : '(Root)';
});

// Check if the file is already in the current folder being viewed
const isFileInCurrentFolder = computed(() => {
    if (!props.file) return false;
    
    // If we're at root and file's parent_id is null or the root folder id
    if (!currentFolderId.value) {
        return !props.file.parent_id || props.file.parent_id === 1;
    }
    
    // If we're in a subfolder, check if file's parent_id matches current folder
    return props.file.parent_id === currentFolderId.value;
});

// Load folders when modal is shown
watch(() => props.show, (newValue) => {
    if (newValue) {
        resetModal();
        loadFolders();
    }
});

const resetModal = () => {
    selectedFolder.value = null;
    isCurrentFolderSelected.value = false;
    currentFolderId.value = null;
    currentPath.value = [];
};

const loadFolders = async (folderId = null) => {
    try {
        let url = route('myFiles');
        if (folderId && currentPath.value.length > 0) {
            // Get the current folder path to navigate to
            const currentFolder = currentPath.value[currentPath.value.length - 1];
            if (currentFolder && currentFolder.path) {
                url = route('myFiles', { folder: currentFolder.path });
            }
        }
        
        const response = await httpGet(url);
        
        // Filter only folders and exclude the file being moved
        let folders = response.data ? response.data.filter(item => 
            item.is_folder && item.id !== props.file?.id
        ) : [];
        
        availableFolders.value = folders;
    } catch (error) {
        console.error('Error loading folders:', error);
        availableFolders.value = [];
    }
};

const navigateToRoot = () => {
    currentFolderId.value = null;
    currentPath.value = [];
    selectedFolder.value = null;
    isCurrentFolderSelected.value = false;
    loadFolders();
};

const navigateToFolder = (folder) => {
    // Add to path if not already there
    const existingIndex = currentPath.value.findIndex(f => f.id === folder.id);
    if (existingIndex !== -1) {
        // Navigate to this folder by removing everything after it
        currentPath.value = currentPath.value.slice(0, existingIndex + 1);
    } else {
        // Add this folder to the path
        currentPath.value.push(folder);
    }
    
    currentFolderId.value = folder.id;
    selectedFolder.value = null;
    isCurrentFolderSelected.value = false;
    loadFolders(folder.id);
};

const navigateBack = () => {
    if (currentPath.value.length > 0) {
        currentPath.value.pop();
        if (currentPath.value.length > 0) {
            const parentFolder = currentPath.value[currentPath.value.length - 1];
            currentFolderId.value = parentFolder.id;
            loadFolders(parentFolder.id);
        } else {
            navigateToRoot();
        }
    } else {
        navigateToRoot();
    }
    selectedFolder.value = null;
    isCurrentFolderSelected.value = false;
};

const selectFolder = (folder) => {
    selectedFolder.value = selectedFolder.value?.id === folder.id ? null : folder;
    isCurrentFolderSelected.value = false;
};

const selectCurrentFolder = () => {
    selectedFolder.value = null;
    isCurrentFolderSelected.value = !isCurrentFolderSelected.value;
};

const confirmMove = async () => {
    if ((!selectedFolder.value && !isCurrentFolderSelected.value) || !props.file) return;
    
    isMoving.value = true;
    
    try {
        let targetFolderId;
        
        if (isCurrentFolderSelected.value) {
            targetFolderId = currentFolderId.value || 1; // Use current folder or root
        } else {
            targetFolderId = selectedFolder.value.id;
        }
        
        const response = await httpPost(route('files.move'), {
            id: props.file.id,
            parent_id: targetFolderId
        });
        
        showSuccessNotification(response.message || 'File moved successfully');
        emit('moved', response.file);
        closeModal();
    } catch (error) {
        console.error('Move error:', error);
        showErrorNotification(error.error?.message || 'Failed to move file');
    } finally {
        isMoving.value = false;
    }
};

const closeModal = () => {
    if (!isMoving.value) {
        resetModal();
        emit('close');
    }
};
</script>