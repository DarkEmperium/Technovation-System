function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = name + "=" + value + "; expires=" + date.toUTCString() + "; path=/";
}

function getCookie(name) {
    const value = "; " + document.cookie;
    const parts = value.split("; " + name + "=");
    if (parts.length === 2) return parts.pop().split(";").shift();
}

// Called when Dismiss button is clicked
function dismissReminder() {
    const monthKey = new Date().toISOString().slice(0, 7); // e.g., '2025-04'
    setCookie("utility_reminder_dismissed_" + monthKey, "true", 7); // Hide for 7 days
}