export const isImage = (file) => {
    return /^image\/\w+$/.test(file.mime);
};

export const isPDF = (file) => {
    return [
        "application/pdf",
        "application/x-pdf",
        "application/acrobat",
        "application/vnd.pdf",
        "text/pdf",
        "text/x-pdf",
    ].includes(file.mime);
};

export const isAudio = (file) => {
    return [
        "audio/mpeg",
        "audio/ogg",
        "audio/wav",
        "audio/x-m4a",
        "audio/webm",
    ].includes(file.mime);
};

export const isVideo = (file) => {
    return [
        "video/mp4",
        "video/mpeg",
        "video/ogg",
        "video/quicktime",
        "video/webm",
    ].includes(file.mime);
};

export const isWord = (file) => {
    return [
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/vnd.ms-word.document.macroEnabled.12",
        "application/vnd.ms-word.template.macroEnabled.12",
    ].includes(file.mime);
};

export const isExcel = (file) => {
    return [
        "application/vnd.ms-excel",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/vnd.ms-excel.sheet.macroEnabled.12",
        "application/vnd.ms-excel.template.macroEnabled.12",
    ].includes(file.mime);
};

export const isZip = (file) => {
    return ["application/zip"].includes(file.mime);
};

export const isText = (file) => {
    return [
        "text/plain",
        "text/html",
        "text/css",
        "text/javascript",
        "text/csv",
    ].includes(file.mime);
};
