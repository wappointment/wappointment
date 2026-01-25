// Auto-generated file - DO NOT EDIT MANUALLY
// Generated from app/Routes/api.php

export const routes = {
    "jobs.list": {
        "method": "GET",
        "path": "/jobs"
    },
    "clients.list": {
        "method": "GET",
        "path": "/clients"
    },
    "clients.create": {
        "method": "POST",
        "path": "/clients"
    },
    "clients.update": {
        "method": "POST",
        "path": "/clients/(?P<id>\\d+)/put"
    },
    "clients.delete": {
        "method": "POST",
        "path": "/clients/(?P<id>\\d+)/delete"
    },
    "settings.list": {
        "method": "GET",
        "path": "/settings"
    },
    "settings.save": {
        "method": "POST",
        "path": "/settings"
    },
    "settings.get": {
        "method": "GET",
        "path": "/settings/(?P<name>[a-zA-Z0-9_-]+)"
    }
};

export function buildRoute(name, params = {}) {
    const route = routes[name];
    if (!route) {
        throw new Error(`Route ${name} not found`);
    }

    let path = route.path;
    // Replace WordPress regex patterns with actual values
    Object.keys(params).forEach(key => {
        path = path.replace(new RegExp(`\\(\\?P<${key}>.*?\\)`), params[key]);
    });

    return { path, method: route.method };
}
