import { usePage } from "@inertiajs/vue3";

export const httpGet = (url) => {
    return fetch(url, {
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
        },
    }).then((response) => {
        return response.json();
    });
};

export const httpPost = (url, data) => {
    return new Promise((resolve, reject) => {
        fetch(url, {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props._csrf_token,
            },
            body: JSON.stringify(data),
        }).then((response) => {
            if (response.ok) {
                return resolve(response.json());
            } else {
                return response.json().then((data) => {
                    reject({ response, error: data });
                });
            }
        });
    });
};
