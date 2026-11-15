// Auto-generated file - DO NOT EDIT MANUALLY
// Generated from app/Routes/api.php
// Run: php bin/generate-routes.php to regenerate

export const routes = {
    "self.calendars": {
        "method": "GET",
        "path": "/me/calendars"
    },
    "self.weekly.save": {
        "method": "POST",
        "path": "/me/calendars/(?P<id>\\d+)/weekly-settings"
    },
    "self.statuses.list": {
        "method": "GET",
        "path": "/me/calendars/(?P<id>\\d+)/statuses"
    },
    "self.statuses.create": {
        "method": "POST",
        "path": "/me/calendars/(?P<id>\\d+)/statuses"
    },
    "self.statuses.delete": {
        "method": "POST",
        "path": "/me/calendars/(?P<id>\\d+)/statuses/(?P<status_id>\\d+)/delete"
    },
    "database.schema": {
        "method": "GET",
        "path": "/system/schema"
    },
    "appointments.list": {
        "method": "GET",
        "path": "/appointments"
    },
    "appointments.status": {
        "method": "POST",
        "path": "/appointments"
    },
    "booking-settings.get": {
        "method": "GET",
        "path": "/booking-settings"
    },
    "booking-settings.save": {
        "method": "POST",
        "path": "/booking-settings"
    },
    "booking.catalog": {
        "method": "GET",
        "path": "/booking/catalog"
    },
    "booking.availability": {
        "method": "POST",
        "path": "/booking/availability"
    },
    "booking.create": {
        "method": "POST",
        "path": "/booking"
    },
    "booking.manage": {
        "method": "POST",
        "path": "/booking/manage"
    },
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